<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\RateLimiter;

class VerificationController extends Controller
{
    /**
     * Maximum resend attempts
     */
    protected $maxResendAttempts = 3;
    
    /**
     * Resend decay time in minutes
     */
    protected $resendDecayMinutes = 60;
    
    /**
     * Show the email verification notice.
     *
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function show()
    {
        $userId = Session::get('user_id');
        
        if (!$userId) {
            return redirect()->route('login');
        }
        
        $user = User::find($userId);
        
        if (!$user) {
            Session::flush();
            return redirect()->route('login');
        }
        
        // Check if already verified
        if ($user->is_verified || $user->email_verified_at) {
            return redirect()->route('account.dashboard');
        }
        
        return view('auth.verify', ['user' => $user]);
    }
    
    /**
     * Verify the user's email address.
     *
     * @param \Illuminate\Http\Request $request
     * @param string $token
     * @return \Illuminate\Http\RedirectResponse
     */
    public function verify(Request $request, $token)
    {
        // Validate token format
        if (!$token || strlen($token) !== 64) {
            Session::flash('error', 'Invalid verification link.');
            return redirect()->route('login');
        }
        
        // Check cache first for performance
        $userId = Cache::get('email_verify_' . $token);
        
        if ($userId) {
            $user = User::find($userId);
        } else {
            // Fallback to database lookup
            $user = User::where('email_verification_token', $token)->first();
        }
        
        // Validate user and token
        if (!$user) {
            Session::flash('error', 'Invalid or expired verification link.');
            return redirect()->route('login');
        }
        
        // Check if token has expired
        if ($user->email_verification_expires && $user->email_verification_expires->isPast()) {
            Session::flash('error', 'Verification link has expired. Please request a new one.');
            return redirect()->route('verification.resend');
        }
        
        // Check if already verified
        if ($user->is_verified || $user->email_verified_at) {
            Session::flash('info', 'Your email is already verified.');
            return redirect()->route('login');
        }
        
        // Verify the user
        $this->markEmailAsVerified($user);
        
        // Auto-login the user
        $this->createUserSession($user);
        
        // Send welcome email
        $this->sendWelcomeEmail($user);
        
        Session::flash('success', 'Email verified successfully! Welcome to FirmwareHub.');
        
        return redirect()->route('account.dashboard');
    }
    
    /**
     * Mark the user's email as verified.
     *
     * @param \App\Models\User $user
     * @return void
     */
    protected function markEmailAsVerified($user)
    {
        $user->update([
            'is_verified' => true,
            'email_verified_at' => now(),
            'email_verification_token' => null,
            'email_verification_expires' => null
        ]);
        
        // Clear cache
        if ($user->email_verification_token) {
            Cache::forget('email_verify_' . $user->email_verification_token);
        }
        
        // Grant welcome bonus
        $this->grantWelcomeBonus($user);
        
        // Log verification
        DB::table('activity_logs')->insert([
            'user_id' => $user->id,
            'action' => 'email_verified',
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'created_at' => now()
        ]);
    }
    
    /**
     * Grant welcome bonus to newly verified user.
     *
     * @param \App\Models\User $user
     * @return void
     */
    protected function grantWelcomeBonus($user)
    {
        // Add welcome loyalty points
        $user->increment('loyalty_points', 50);
        
        // Check if referred user - grant referrer bonus
        if ($user->referred_by) {
            $referrer = User::find($user->referred_by);
            if ($referrer) {
                $referrer->increment('loyalty_points', 200);
                $referrer->increment('referral_earnings', 5.00);
                
                // Update referral log
                DB::table('referral_logs')
                    ->where('referred_id', $user->id)
                    ->update(['status' => 'completed', 'updated_at' => now()]);
            }
        }
    }
    
    /**
     * Resend the email verification notification.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function resend(Request $request)
    {
        // Validate request
        $request->validate([
            'email' => 'required|email|exists:users,email'
        ]);
        
        // Check rate limiting
        if ($this->hasTooManyResendAttempts($request)) {
            return $this->sendResendLockoutResponse($request);
        }
        
        $user = User::where('email', $request->input('email'))->first();
        
        // Check if already verified
        if ($user->is_verified || $user->email_verified_at) {
            Session::flash('info', 'Your email is already verified.');
            return redirect()->route('login');
        }
        
        // Generate new token
        $this->generateNewVerificationToken($user);
        
        // Send verification email
        $this->sendVerificationEmail($user);
        
        // Increment attempts
        $this->incrementResendAttempts($request);
        
        Session::flash('success', 'Verification email has been resent. Please check your inbox.');
        
        return back();
    }
    
    /**
     * Generate new verification token.
     *
     * @param \App\Models\User $user
     * @return void
     */
    protected function generateNewVerificationToken($user)
    {
        // Clear old token from cache
        if ($user->email_verification_token) {
            Cache::forget('email_verify_' . $user->email_verification_token);
        }
        
        $token = bin2hex(random_bytes(32));
        
        $user->update([
            'email_verification_token' => $token,
            'email_verification_expires' => now()->addHours(24)
        ]);
        
        // Store in cache
        Cache::put('email_verify_' . $token, $user->id, 86400); // 24 hours
    }
    
    /**
     * Send verification email.
     *
     * @param \App\Models\User $user
     * @return void
     */
    protected function sendVerificationEmail($user)
    {
        DB::table('email_queue')->insert([
            'to' => $user->email,
            'subject' => 'Verify Your Email - FirmwareHub',
            'template' => 'emails.verify',
            'data' => json_encode([
                'user_name' => $user->first_name,
                'verification_url' => route('verification.verify', [
                    'token' => $user->email_verification_token
                ]),
                'expires_in' => '24 hours'
            ]),
            'priority' => 'high',
            'created_at' => now()
        ]);
    }
    
    /**
     * Send welcome email to verified user.
     *
     * @param \App\Models\User $user
     * @return void
     */
    protected function sendWelcomeEmail($user)
    {
        DB::table('email_queue')->insert([
            'to' => $user->email,
            'subject' => 'Welcome to FirmwareHub!',
            'template' => 'emails.welcome',
            'data' => json_encode([
                'user_name' => $user->first_name,
                'dashboard_url' => route('account.dashboard'),
                'browse_url' => route('products.index'),
                'loyalty_points' => $user->loyalty_points
            ]),
            'priority' => 'normal',
            'created_at' => now()
        ]);
    }
    
    /**
     * Create user session after verification.
     *
     * @param \App\Models\User $user
     * @return void
     */
    protected function createUserSession($user)
    {
        Session::regenerate();
        
        Session::put('user_id', $user->id);
        Session::put('user_email', $user->email);
        Session::put('user_name', $user->full_name);
        Session::put('user_role', $user->role);
        Session::put('logged_in_at', now()->timestamp);
        
        // Check subscription status
        if ($user->hasActiveSubscription()) {
            Session::put('has_subscription', true);
        }
        
        // Store session in cache
        Cache::put('user_session_' . $user->id, [
            'session_id' => Session::getId(),
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'last_activity' => now()
        ], 120); // 2 hours
        
        // Update user login info
        $user->update([
            'last_login_at' => now(),
            'last_login_ip' => request()->ip()
        ]);
    }
    
    /**
     * Check if has too many resend attempts.
     *
     * @param \Illuminate\Http\Request $request
     * @return bool
     */
    protected function hasTooManyResendAttempts(Request $request)
    {
        return RateLimiter::tooManyAttempts(
            $this->resendThrottleKey($request),
            $this->maxResendAttempts
        );
    }
    
    /**
     * Increment resend attempts.
     *
     * @param \Illuminate\Http\Request $request
     * @return void
     */
    protected function incrementResendAttempts(Request $request)
    {
        RateLimiter::hit(
            $this->resendThrottleKey($request),
            $this->resendDecayMinutes * 60
        );
    }
    
    /**
     * Send resend lockout response.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function sendResendLockoutResponse(Request $request)
    {
        $seconds = RateLimiter::availableIn($this->resendThrottleKey($request));
        $minutes = ceil($seconds / 60);
        
        Session::flash('error', "Too many resend attempts. Please try again in {$minutes} minutes.");
        
        return back();
    }
    
    /**
     * Get resend throttle key.
     *
     * @param \Illuminate\Http\Request $request
     * @return string
     */
    protected function resendThrottleKey(Request $request)
    {
        return 'verify_resend_' . $request->input('email') . '|' . $request->ip();
    }
}