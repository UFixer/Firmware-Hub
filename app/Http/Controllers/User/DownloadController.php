<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\File;
use App\Models\Download;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DownloadController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('check.bandwidth'); // Custom middleware for bandwidth checking
    }
    
    // Show download history
    public function index()
    {
        $downloads = Auth::user()->downloads()
                                ->with('file.category')
                                ->orderBy('created_at', 'desc')
                                ->paginate(20);
        
        $stats = [
            'total' => Auth::user()->downloads()->count(),
            'today' => Auth::user()->downloads()->whereDate('created_at', today())->count(),
            'month' => Auth::user()->downloads()->whereMonth('created_at', now()->month)->count(),
        ];
        
        return view('user.downloads', compact('downloads', 'stats'));
    }
    
    // Initiate download with bandwidth check
    public function download(Request $request, $fileId)
    {
        $file = File::findOrFail($fileId);
        $user = Auth::user();
        
        // Check if user has active subscription
        if (!$user->hasActiveSubscription()) {
            return back()->with('error', 'Active subscription required for downloads');
        }
        
        // Check bandwidth limits
        $bandwidthCheck = $this->checkBandwidthLimit($user, $file);
        if (!$bandwidthCheck['allowed']) {
            return back()->with('error', $bandwidthCheck['message']);
        }
        
        // Check daily download limits
        $dailyLimit = $user->subscription->package->daily_downloads ?? 10;
        $todayDownloads = $user->downloads()->whereDate('created_at', today())->count();
        
        if ($todayDownloads >= $dailyLimit) {
            return back()->with('error', 'Daily download limit reached. Try again tomorrow.');
        }
        
        // Record download
        DB::transaction(function () use ($user, $file) {
            Download::create([
                'user_id' => $user->id,
                'file_id' => $file->id,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'downloaded_at' => now(),
            ]);
            
            // Update file download counter
            $file->increment('download_count');
            
            // Update user bandwidth usage (stored in file cache)
            $this->updateBandwidthUsage($user, $file);
        });
        
        // Generate temporary download URL (expires in 1 hour)
        $downloadUrl = $this->generateTemporaryUrl($file);
        
        return redirect($downloadUrl);
    }
    
    // Check bandwidth limits
    private function checkBandwidthLimit($user, $file)
    {
        $subscription = $user->subscription;
        if (!$subscription) {
            return ['allowed' => false, 'message' => 'No active subscription'];
        }
        
        $package = $subscription->package;
        $monthlyLimitMB = $package->bandwidth_limit ?? 10240; // Default 10GB
        
        // Get current month usage from file cache
        $usageMB = $this->getMonthlyBandwidthUsage($user);
        $fileSizeMB = ($file->size_bytes ?? 0) / 1048576;
        
        if (($usageMB + $fileSizeMB) > $monthlyLimitMB) {
            $remaining = max(0, $monthlyLimitMB - $usageMB);
            return [
                'allowed' => false,
                'message' => sprintf(
                    'Bandwidth limit exceeded. You have %.2f MB remaining this month.',
                    $remaining
                )
            ];
        }
        
        return ['allowed' => true];
    }
    
    // Get monthly bandwidth usage from file cache
    private function getMonthlyBandwidthUsage($user)
    {
        $cacheKey = 'bandwidth_' . $user->id . '_' . date('Y_m');
        $cacheFile = storage_path('framework/cache/bandwidth/' . $cacheKey . '.txt');
        
        if (file_exists($cacheFile)) {
            return (float) file_get_contents($cacheFile);
        }
        
        return 0;
    }
    
    // Update bandwidth usage in file cache
    private function updateBandwidthUsage($user, $file)
    {
        $cacheKey = 'bandwidth_' . $user->id . '_' . date('Y_m');
        $cacheDir = storage_path('framework/cache/bandwidth');
        
        // Create directory if doesn't exist
        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0755, true);
        }
        
        $cacheFile = $cacheDir . '/' . $cacheKey . '.txt';
        $currentUsage = $this->getMonthlyBandwidthUsage($user);
        $fileSizeMB = ($file->size_bytes ?? 0) / 1048576;
        
        file_put_contents($cacheFile, $currentUsage + $fileSizeMB);
        
        // Clean old bandwidth files (older than 2 months)
        $this->cleanOldBandwidthFiles($cacheDir);
    }
    
    // Generate temporary download URL
    private function generateTemporaryUrl($file)
    {
        // Create signed URL that expires in 1 hour
        $token = hash_hmac('sha256', $file->id . time(), env('APP_KEY'));
        $expiry = time() + 3600; // 1 hour
        
        // Store token in file cache
        $cacheDir = storage_path('framework/cache/download_tokens');
        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0755, true);
        }
        
        $tokenData = [
            'file_id' => $file->id,
            'user_id' => Auth::id(),
            'expiry' => $expiry,
            'url' => $file->download_url // External URL
        ];
        
        file_put_contents(
            $cacheDir . '/' . $token . '.json',
            json_encode($tokenData)
        );
        
        // Return the actual external URL (not local file)
        // In production, this would redirect through your server for tracking
        return $file->download_url;
    }
    
    // Clean old bandwidth cache files
    private function cleanOldBandwidthFiles($dir)
    {
        $files = glob($dir . '/*.txt');
        $twoMonthsAgo = strtotime('-2 months');
        
        foreach ($files as $file) {
            if (filemtime($file) < $twoMonthsAgo) {
                unlink($file);
            }
        }
    }
    
    // Re-download previously downloaded file
    public function redownload($downloadId)
    {
        $download = Download::where('user_id', Auth::id())
                           ->findOrFail($downloadId);
        
        // Allow re-download within 7 days
        if ($download->downloaded_at->diffInDays(now()) > 7) {
            return back()->with('error', 'Re-download period expired. Please download again.');
        }
        
        return $this->download(request(), $download->file_id);
    }
}