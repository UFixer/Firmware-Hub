<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Cache;
use App\Models\User;
use App\Models\Subscription;

class CheckBandwidth
{
    /**
     * Handle an incoming request for bandwidth-limited operations.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @param int|null $requiredBytes
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $requiredBytes = null)
    {
        // Get authenticated user
        $userId = Session::get('user_id');
        
        if (!$userId) {
            return redirect()->route('login');
        }
        
        $user = User::find($userId);
        
        if (!$user) {
            return redirect()->route('login');
        }
        
        // Check if user has active subscription
        $subscription = $user->activeSubscription;
        
        if (!$subscription) {
            Session::flash('error', 'You need an active subscription to download files.');
            return redirect()->route('packages.index');
        }
        
        // Check daily download limit
        if ($this->isDailyLimitExceeded($subscription)) {
            Session::flash('error', 'Daily download limit reached. Try again tomorrow.');
            return response()->json(['error' => 'Daily limit exceeded'], 429);
        }
        
        // Check monthly download limit
        if ($this->isMonthlyLimitExceeded($subscription)) {
            Session::flash('error', 'Monthly download limit reached.');
            return response()->json(['error' => 'Monthly limit exceeded'], 429);
        }
        
        // Check bandwidth limit
        if ($this->isBandwidthExceeded($subscription, $requiredBytes)) {
            $resetDate = $subscription->bandwidth_reset_date->format('F j, Y');
            Session::flash('error', "Bandwidth limit exceeded. Resets on {$resetDate}.");
            return response()->json(['error' => 'Bandwidth limit exceeded'], 429);
        }
        
        // Store subscription in request for later use
        $request->attributes->set('subscription', $subscription);
        
        return $next($request);
    }
    
    /**
     * Check if daily download limit is exceeded.
     *
     * @param \App\Models\Subscription $subscription
     * @return bool
     */
    protected function isDailyLimitExceeded($subscription)
    {
        // Reset daily counter if needed
        $lastReset = Cache::get('daily_reset_' . $subscription->id);
        if (!$lastReset || $lastReset < now()->startOfDay()) {
            $subscription->update(['downloads_used_today' => 0]);
            Cache::put('daily_reset_' . $subscription->id, now()->startOfDay(), 86400);
        }
        
        return $subscription->downloads_used_today >= $subscription->daily_limit;
    }
    
    /**
     * Check if monthly download limit is exceeded.
     *
     * @param \App\Models\Subscription $subscription
     * @return bool
     */
    protected function isMonthlyLimitExceeded($subscription)
    {
        // Reset monthly counter if needed
        if ($subscription->bandwidth_reset_date && $subscription->bandwidth_reset_date->isPast()) {
            $subscription->resetMonthlyCounters();
        }
        
        return $subscription->downloads_used_month >= $subscription->monthly_limit;
    }
    
    /**
     * Check if bandwidth limit is exceeded.
     *
     * @param \App\Models\Subscription $subscription
     * @param int|null $requiredBytes
     * @return bool
     */
    protected function isBandwidthExceeded($subscription, $requiredBytes = null)
    {
        // If no specific bytes required, check if any bandwidth left
        if (!$requiredBytes) {
            return $subscription->bandwidth_used_bytes >= $subscription->monthly_bandwidth_limit;
        }
        
        // Check if required bytes would exceed limit
        $projectedUsage = $subscription->bandwidth_used_bytes + $requiredBytes;
        return $projectedUsage > $subscription->monthly_bandwidth_limit;
    }
}