<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    
    // Show user profile
    public function index()
    {
        $user = Auth::user();
        $subscription = $user->subscription;
        $recentDownloads = $user->downloads()
                                ->with('file')
                                ->latest()
                                ->take(10)
                                ->get();
        
        return view('user.profile', compact('user', 'subscription', 'recentDownloads'));
    }
    
    // Update profile form
    public function edit()
    {
        $user = Auth::user();
        return view('user.profile-edit', compact('user'));
    }
    
    // Update profile
    public function update(Request $request)
    {
        $user = Auth::user();
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
            'avatar_url' => 'nullable|url|max:500', // External URL only
        ]);
        
        // Update user profile
        $user->update($validated);
        
        return redirect()->route('profile.index')
                       ->with('success', 'Profile updated successfully');
    }
    
    // Change password form
    public function passwordForm()
    {
        return view('user.change-password');
    }
    
    // Update password
    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => 'required',
            'password' => 'required|min:8|confirmed',
        ]);
        
        $user = Auth::user();
        
        // Verify current password
        if (!Hash::check($validated['current_password'], $user->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect']);
        }
        
        // Update password
        $user->update([
            'password' => Hash::make($validated['password'])
        ]);
        
        return redirect()->route('profile.index')
                       ->with('success', 'Password changed successfully');
    }
    
    // Show user statistics
    public function statistics()
    {
        $user = Auth::user();
        
        $stats = [
            'total_downloads' => $user->downloads()->count(),
            'monthly_downloads' => $user->downloads()
                                      ->whereMonth('created_at', now()->month)
                                      ->count(),
            'bandwidth_used' => $this->calculateBandwidthUsed($user),
            'subscription_days_left' => $this->getSubscriptionDaysLeft($user),
        ];
        
        return view('user.statistics', compact('stats'));
    }
    
    // Calculate bandwidth used (MB)
    private function calculateBandwidthUsed($user)
    {
        $downloads = $user->downloads()
                         ->whereMonth('created_at', now()->month)
                         ->get();
        
        $totalBytes = 0;
        foreach ($downloads as $download) {
            // Estimate file size (stored in files table)
            $totalBytes += $download->file->size_bytes ?? 0;
        }
        
        return round($totalBytes / 1048576, 2); // Convert to MB
    }
    
    // Get subscription days remaining
    private function getSubscriptionDaysLeft($user)
    {
        if (!$user->subscription || !$user->subscription->is_active) {
            return 0;
        }
        
        $endsAt = $user->subscription->ends_at;
        return max(0, now()->diffInDays($endsAt, false));
    }
}