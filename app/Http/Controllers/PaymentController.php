<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Package;
use App\Models\Subscription;
use App\Models\Coupon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Stripe\Stripe;
use Stripe\Checkout\Session as StripeSession;
use PayPal\Rest\ApiContext;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Api\Payment;
use PayPal\Api\PaymentExecution;

class PaymentController extends Controller
{
    private $stripeSecret;
    private $paypalContext;
    
    public function __construct()
    {
        $this->middleware('auth');
        
        // Initialize Stripe
        $this->stripeSecret = env('STRIPE_SECRET_KEY');
        if ($this->stripeSecret) {
            Stripe::setApiKey($this->stripeSecret);
        }
        
        // Initialize PayPal
        if (env('PAYPAL_CLIENT_ID') && env('PAYPAL_SECRET')) {
            $this->paypalContext = new ApiContext(
                new OAuthTokenCredential(
                    env('PAYPAL_CLIENT_ID'),
                    env('PAYPAL_SECRET')
                )
            );
            $this->paypalContext->setConfig([
                'mode' => env('PAYPAL_MODE', 'sandbox'),
                'http.ConnectionTimeOut' => 30,
                'log.LogEnabled' => false,
            ]);
        }
    }
    
    // Process checkout with selected payment method
    public function checkout(Request $request)
    {
        $validated = $request->validate([
            'package_id' => 'required|exists:packages,id',
            'payment_method' => 'required|in:stripe,paypal',
            'coupon_code' => 'nullable|string'
        ]);
        
        $package = Package::findOrFail($validated['package_id']);
        $amount = $package->price;
        
        // Apply coupon if provided
        if ($validated['coupon_code']) {
            $coupon = Coupon::where('code', $validated['coupon_code'])
                           ->where('is_active', true)
                           ->first();
            
            if ($coupon && $coupon->isValid()) {
                $amount = $coupon->calculateDiscount($amount);
                $coupon->increment('uses');
            }
        }
        
        // Process based on payment method
        if ($validated['payment_method'] === 'stripe') {
            return $this->processStripe($package, $amount);
        } else {
            return $this->processPayPal($package, $amount);
        }
    }
    
    // Process Stripe payment
    private function processStripe($package, $amount)
    {
        try {
            $session = StripeSession::create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => 'usd',
                        'product_data' => [
                            'name' => $package->name,
                            'description' => $package->description,
                        ],
                        'unit_amount' => $amount * 100, // Convert to cents
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'success_url' => route('payment.success') . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => route('payment.cancel'),
                'metadata' => [
                    'package_id' => $package->id,
                    'user_id' => auth()->id(),
                ],
            ]);
            
            return redirect($session->url);
        } catch (\Exception $e) {
            return back()->with('error', 'Payment processing failed. Please try again.');
        }
    }
    
    // Process PayPal payment
    private function processPayPal($package, $amount)
    {
        // PayPal implementation (simplified for shared hosting)
        $order = Order::create([
            'user_id' => auth()->id(),
            'package_id' => $package->id,
            'amount' => $amount,
            'payment_method' => 'paypal',
            'status' => 'pending',
            'transaction_id' => uniqid('PP_'),
        ]);
        
        // In production, integrate with PayPal API
        return redirect()->route('payment.paypal.execute', ['order' => $order->id]);
    }
    
    // Handle successful payment
    public function success(Request $request)
    {
        if ($request->has('session_id')) {
            // Verify Stripe session
            $session = StripeSession::retrieve($request->session_id);
            
            if ($session->payment_status === 'paid') {
                $this->activateSubscription(
                    $session->metadata->user_id,
                    $session->metadata->package_id,
                    $session->id
                );
            }
        }
        
        return view('payment.success');
    }
    
    // Activate user subscription
    private function activateSubscription($userId, $packageId, $transactionId)
    {
        DB::transaction(function () use ($userId, $packageId, $transactionId) {
            // Create order record
            Order::create([
                'user_id' => $userId,
                'package_id' => $packageId,
                'amount' => Package::find($packageId)->price,
                'payment_method' => 'stripe',
                'status' => 'completed',
                'transaction_id' => $transactionId,
            ]);
            
            // Update or create subscription
            $package = Package::find($packageId);
            Subscription::updateOrCreate(
                ['user_id' => $userId],
                [
                    'package_id' => $packageId,
                    'starts_at' => now(),
                    'ends_at' => now()->addDays($package->duration_days),
                    'is_active' => true,
                ]
            );
        });
    }
}