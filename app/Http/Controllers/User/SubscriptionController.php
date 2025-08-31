<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\Subscription;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SubscriptionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    
    // Show subscription details
    public function index()
    {
        $user = Auth::user();
        $subscription = $user->subscription;
        $packages = Package::where('is_active', true)
                          ->orderBy('price', 'asc')
                          ->get();
        
        $history = Order::where('user_id', $user->id)
                       ->with('package')
                       ->orderBy('created_at', 'desc')
                       ->take(10)
                       ->get();
        
        return view('user.subscription', compact('subscription', 'packages', 'history'));
    }
    
    // Upgrade/change subscription
    public function upgrade(Request $request)
    {
        $validated = $request->validate([
            'package_id' => 'required|exists:packages,id'
        ]);
        
        $user = Auth::user();
        $package = Package::findOrFail($validated['package_id']);
        $currentSub = $user->subscription;
        
        // Check if downgrading
        if ($currentSub && $currentSub->package->price > $package->price) {
            return back()->with('error', 'Please contact support for downgrades');
        }
        
        // Calculate prorated amount if upgrading
        $amount = $package->price;
        if ($currentSub && $currentSub->is_active) {
            $daysRemaining = now()->diffInDays($currentSub->ends_at, false);
            if ($daysRemaining > 0) {
                $dailyRate = $currentSub->package->price / $currentSub->package->duration_days;
                $credit = $dailyRate * $daysRemaining;
                $amount = max(0, $package->price - $credit);
            }
        }
        
        // Store upgrade request in session
        session([
            'upgrade_package_id' => $package->id,
            'upgrade_amount' => $amount,
            'upgrade_credit' => $credit ?? 0
        ]);
        
        return redirect()->route('payment.checkout')
                       ->with('info', 'Prorated amount: $' . number_format($amount, 2));
    }
    
    // Cancel subscription
    public function cancel(Request $request)
    {
        $user = Auth::user();
        $subscription = $user->subscription;
        
        if (!$subscription || !$subscription->is_active) {
            return back()->with('error', 'No active subscription to cancel');
        }
        
        // Set to cancel at period end
        $subscription->update([
            'is_active' => false,
            'cancelled_at' => now()
        ]);
        
        return redirect()->route('subscription.index')
                       ->with('success', 'Subscription will end on ' . $subscription->ends_at->format('M d, Y'));
    }
    
    // Reactivate cancelled subscription
    public function reactivate(Request $request)
    {
        $user = Auth::user();
        $subscription = $user->subscription;
        
        if (!$subscription || $subscription->is_active) {
            return back()->with('error', 'Cannot reactivate this subscription');
        }
        
        // Check if still within subscription period
        if ($subscription->ends_at->isFuture()) {
            $subscription->update([
                'is_active' => true,
                'cancelled_at' => null
            ]);
            
            return redirect()->route('subscription.index')
                           ->with('success', 'Subscription reactivated successfully');
        }
        
        return back()->with('error', 'Subscription has expired. Please purchase a new plan.');
    }
    
    // Show available packages
    public function packages()
    {
        $packages = Package::where('is_active', true)
                          ->orderBy('price', 'asc')
                          ->get();
        
        $currentPackageId = Auth::user()->subscription->package_id ?? null;
        
        return view('user.packages', compact('packages', 'currentPackageId'));
    }
    
    // Check subscription status (AJAX)
    public function checkStatus()
    {
        $user = Auth::user();
        $subscription = $user->subscription;
        
        if (!$subscription) {
            return response()->json([
                'active' => false,
                'message' => 'No subscription'
            ]);
        }
        
        // Check if expired
        if ($subscription->ends_at->isPast()) {
            $subscription->update(['is_active' => false]);
            
            return response()->json([
                'active' => false,
                'message' => 'Subscription expired',
                'expired_at' => $subscription->ends_at->toDateString()
            ]);
        }
        
        return response()->json([
            'active' => $subscription->is_active,
            'package' => $subscription->package->name,
            'ends_at' => $subscription->ends_at->toDateString(),
            'days_remaining' => now()->diffInDays($subscription->ends_at, false)
        ]);
    }
}