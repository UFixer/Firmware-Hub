<?php

namespace App\Http\Controllers;

use App\Models\File;
use App\Models\Category;
use App\Models\Download;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use App\Helpers\AuthHelper;

class FileController extends Controller
{
    /**
     * Items per page
     */
    protected $perPage = 20;
    
    /**
     * Cache duration (2 hours)
     */
    protected $cacheTime = 7200;
    
    /**
     * Display listing of files.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $query = File::public()->with(['category']);
        
        // Filter by category
        if ($request->has('category')) {
            $category = Category::where('slug', $request->category)->first();
            if ($category) {
                $query->where('category_id', $category->id);
            }
        }
        
        // Filter by brand
        if ($request->has('brand')) {
            $query->where('brand', $request->brand);
        }
        
        // Filter by model
        if ($request->has('model')) {
            $query->where('model', $request->model);
        }
        
        // Filter by platform
        if ($request->has('platform')) {
            $query->where('platform', $request->platform);
        }
        
        // Filter by type
        if ($request->has('type')) {
            $query->where('file_type', $request->type);
        }
        
        // Price filter
        if ($request->has('free_only')) {
            $query->where('is_premium', false);
        }
        
        // Sorting
        $sortBy = $request->get('sort', 'latest');
        switch ($sortBy) {
            case 'popular':
                $query->orderBy('download_count', 'desc');
                break;
            case 'name':
                $query->orderBy('name', 'asc');
                break;
            case 'size':
                $query->orderBy('size', 'desc');
                break;
            case 'oldest':
                $query->orderBy('created_at', 'asc');
                break;
            default: // latest
                $query->orderBy('created_at', 'desc');
        }
        
        // Paginate results
        $files = $query->paginate($this->perPage);
        
        // Get filters data (cached)
        $filters = $this->getFilters();
        
        return view('files.index', compact('files', 'filters'));
    }
    
    /**
     * Display file details.
     *
     * @param string $slug
     * @return \Illuminate\View\View
     */
    public function show($slug)
    {
        $file = File::where('slug', $slug)
            ->with(['category', 'uploader', 'reviews'])
            ->firstOrFail();
        
        // Check if file is accessible
        if (!$file->is_public) {
            if (!AuthHelper::check()) {
                return redirect()->route('login')
                    ->with('error', 'Please login to view this file.');
            }
            
            if ($file->is_premium && !AuthHelper::hasSubscription()) {
                return redirect()->route('packages.index')
                    ->with('error', 'Premium subscription required to access this file.');
            }
        }
        
        // Increment view count (throttled per session)
        $viewKey = 'file_viewed_' . $file->id;
        if (!Session::has($viewKey)) {
            $file->increment('view_count');
            Session::put($viewKey, true);
        }
        
        // Get related files (cached)
        $relatedFiles = Cache::remember('related_files_' . $file->id, $this->cacheTime, function () use ($file) {
            return File::public()
                ->where('id', '!=', $file->id)
                ->where(function ($query) use ($file) {
                    $query->where('category_id', $file->category_id)
                        ->orWhere('brand', $file->brand)
                        ->orWhere('model', $file->model);
                })
                ->limit(6)
                ->get();
        });
        
        // Get user's download history for this file
        $userDownload = null;
        if (AuthHelper::check()) {
            $userDownload = Download::where('user_id', AuthHelper::id())
                ->where('file_id', $file->id)
                ->where('status', 'completed')
                ->latest()
                ->first();
        }
        
        // Check download eligibility
        $canDownload = $this->checkDownloadEligibility($file);
        
        return view('files.show', compact('file', 'relatedFiles', 'userDownload', 'canDownload'));
    }
    
    /**
     * Handle file download request.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function download(Request $request, $id)
    {
        $file = File::findOrFail($id);
        
        // Check authentication
        if (!AuthHelper::check()) {
            return redirect()->route('login')
                ->with('error', 'Please login to download files.');
        }
        
        // Check download eligibility
        $eligibility = $this->checkDownloadEligibility($file);
        if (!$eligibility['can_download']) {
            return redirect()->back()->with('error', $eligibility['message']);
        }
        
        // Get user and subscription
        $user = AuthHelper::user();
        $subscription = $user->activeSubscription;
        
        // Check bandwidth limit
        if ($subscription) {
            $remainingBandwidth = $subscription->getRemainingBandwidthBytes();
            if ($file->size > $remainingBandwidth) {
                return redirect()->back()->with('error', 'Insufficient bandwidth. Please upgrade your plan.');
            }
        }
        
        // Create download record
        $download = Download::create([
            'download_id' => $this->generateDownloadId(),
            'token' => bin2hex(random_bytes(32)),
            'user_id' => $user->id,
            'subscription_id' => $subscription ? $subscription->id : null,
            'user_email' => $user->email,
            'file_id' => $file->id,
            'file_name' => $file->name,
            'file_version' => $file->version,
            'file_url' => $file->file_url,
            'file_size_bytes' => $file->size,
            'file_size_formatted' => $file->size_formatted,
            'status' => 'pending',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'expires_at' => now()->addHours(24)
        ]);
        
        // Generate secure download URL (external)
        $downloadUrl = $this->generateSecureDownloadUrl($file, $download);
        
        // Update subscription counters
        if ($subscription) {
            $subscription->increment('downloads_used_today');
            $subscription->increment('downloads_used_month');
            $subscription->update(['last_download_at' => now()]);
        }
        
        // Log activity
        DB::table('activity_logs')->insert([
            'user_id' => $user->id,
            'action' => 'file_download',
            'model_type' => 'File',
            'model_id' => $file->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'created_at' => now()
        ]);
        
        // Redirect to external download URL
        return redirect($downloadUrl);
    }
    
    /**
     * Search files.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\View\View
     */
    public function search(Request $request)
    {
        $request->validate([
            'q' => 'required|string|min:3|max:100'
        ]);
        
        $query = $request->get('q');
        
        // Search in files (using full-text index)
        $files = File::public()
            ->where(function ($q) use ($query) {
                $q->where('name', 'LIKE', '%' . $query . '%')
                    ->orWhere('description', 'LIKE', '%' . $query . '%')
                    ->orWhere('brand', 'LIKE', '%' . $query . '%')
                    ->orWhere('model', 'LIKE', '%' . $query . '%')
                    ->orWhere('version', 'LIKE', '%' . $query . '%');
            })
            ->with(['category'])
            ->paginate($this->perPage);
        
        // Log search query
        DB::table('search_logs')->insert([
            'query' => $query,
            'results_count' => $files->total(),
            'user_id' => AuthHelper::id(),
            'ip_address' => $request->ip(),
            'created_at' => now()
        ]);
        
        return view('files.search', compact('files', 'query'));
    }
    
    /**
     * Get filter options for files.
     *
     * @return array
     */
    protected function getFilters()
    {
        return Cache::remember('file_filters', $this->cacheTime, function () {
            return [
                'categories' => Category::active()
                    ->withCount('activeFiles')
                    ->orderBy('name')
                    ->get(),
                'brands' => DB::table('files')
                    ->select('brand', DB::raw('COUNT(*) as count'))
                    ->whereNotNull('brand')
                    ->where('status', 'active')
                    ->where('is_public', true)
                    ->groupBy('brand')
                    ->orderBy('brand')
                    ->get(),
                'platforms' => DB::table('files')
                    ->select('platform', DB::raw('COUNT(*) as count'))
                    ->whereNotNull('platform')
                    ->where('status', 'active')
                    ->where('is_public', true)
                    ->groupBy('platform')
                    ->get(),
                'types' => DB::table('files')
                    ->select('file_type', DB::raw('COUNT(*) as count'))
                    ->where('status', 'active')
                    ->where('is_public', true)
                    ->groupBy('file_type')
                    ->get()
            ];
        });
    }
    
    /**
     * Check if user can download file.
     *
     * @param \App\Models\File $file
     * @return array
     */
    protected function checkDownloadEligibility($file)
    {
        // Check if file is available
        if (!$file->isAvailable()) {
            return ['can_download' => false, 'message' => 'File is not available for download.'];
        }
        
        // Check authentication
        if (!AuthHelper::check()) {
            return ['can_download' => false, 'message' => 'Please login to download.'];
        }
        
        $user = AuthHelper::user();
        
        // Check if premium file
        if ($file->is_premium) {
            if (!$user->hasActiveSubscription()) {
                return ['can_download' => false, 'message' => 'Premium subscription required.'];
            }
            
            $subscription = $user->activeSubscription;
            
            // Check daily limit
            if ($subscription->hasReachedDailyDownloadLimit()) {
                return ['can_download' => false, 'message' => 'Daily download limit reached.'];
            }
            
            // Check monthly limit
            if ($subscription->hasReachedMonthlyDownloadLimit()) {
                return ['can_download' => false, 'message' => 'Monthly download limit reached.'];
            }
            
            // Check bandwidth
            if ($subscription->hasReachedBandwidthLimit()) {
                return ['can_download' => false, 'message' => 'Bandwidth limit exceeded.'];
            }
        }
        
        return ['can_download' => true, 'message' => 'Ready to download'];
    }
    
    /**
     * Generate unique download ID.
     *
     * @return string
     */
    protected function generateDownloadId()
    {
        do {
            $id = 'DL-' . date('YmdHis') . '-' . strtoupper(substr(md5(uniqid()), 0, 6));
        } while (Download::where('download_id', $id)->exists());
        
        return $id;
    }
    
    /**
     * Generate secure download URL.
     *
     * @param \App\Models\File $file
     * @param \App\Models\Download $download
     * @return string
     */
    protected function generateSecureDownloadUrl($file, $download)
    {
        // For external URLs, generate a signed URL or redirect directly
        // This is a simplified version - implement according to your CDN setup
        
        // If using signed URLs with CDN
        $expires = time() + 3600; // 1 hour expiry
        $path = parse_url($file->file_url, PHP_URL_PATH);
        $secret = config('app.cdn_secret');
        
        $signature = hash_hmac('sha256', $path . $expires, $secret);
        
        return $file->file_url . '?token=' . $download->token . '&expires=' . $expires . '&signature=' . $signature;
    }
}