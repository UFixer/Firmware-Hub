<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class ForgotPasswordController extends Controller
{
    /**
     * Maximum reset link requests
     */
    protected $maxAttempts = 3;
    
    /**
     * Decay time in minutes
     */
    protected $decayMinutes = 60;
    
    /**
     * Token expiry time in minutes
     */
    protected $tokenExpiryMinutes = 60;
    
    /**
     * Show the form to request a password reset link.
     *
     * @return \Illuminate\View\View
     */
    public function showLinkRequestForm()
    {
        return view('auth.passwords.email');
    }
    
    /**
     * Send a reset link to the given user.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function sendResetLinkEmail(Request $request)
    {
        // Validate email
        $request->validate([
            'email' => 'required|email|max:100'
        ]);
        
        $email = strtolower($request->input('email'));
        
        // Check rate limiting
        if ($this->hasTooManyAttempts($request)) {
            return $this->sendLockoutResponse($request);
        }
        
        // Find user
        $user = User::where('email', $email)->first();
        
        if (!$user) {
            // Don't reveal if email exists - security measure
            Session::flash('success', 'If your email exists in our system, you will receive a password reset link.');
            return back();
        }
        
        // Check if user is active
        if ($user->status !== 'active') {
            Session::flash('error', 'Your account is not active. Please contact support.');
            return back();
        }
        
        // Check if a recent token exists
        if ($this->recentTokenExists($user)) {
            Session::flash('info', 'A password reset link was recently sent. Please check your email or wait before requesting another.');
            return back();
        }
        
        // Generate password reset token
        $token = $this->generatePasswordResetToken($user);
        
        // Send password reset email
        $this->sendPasswordResetEmail($user, $token);
        
        // Log the password reset request
        $this->logPasswordResetRequest($user);
        
        // Increment attempts
        $this->incrementAttempts($request);
        
        Session::flash('success', 'If your email exists in our system, you will receive a password reset link.');
        
        return back();
    }
    
    /**
     * Generate password reset token.
     *
     * @param \App\Models\User $user
     * @return string
     */
    protected function generatePasswordResetToken($user)
    {
        // Generate unique token
        $token = hash_hmac('sha256', Str::random(40), config('app.key'));
        
        // Store token in database
        $user->update([
            'password_reset_token' => $token,
            'password_reset_expires' => now()->addMinutes($this->tokenExpiryMinutes)
        ]);
        
        // Store in cache for quick lookup
        Cache::put('pwd_reset_' . $token, $user->id, $this->tokenExpiryMinutes * 60);
        
        // Store token request metadata
        Cache::put('pwd_reset_meta_' . $user->id, [
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'requested_at' => now()
        ], $this->tokenExpiryMinutes * 60);
        
        return $token;
    }
    
    /**
     * Check if a recent token exists.
     *
     * @param \App\Models\User $user
     * @return bool
     */
    protected function recentTokenExists($user)
    {
        // Check if token exists and hasn't expired
        if ($user->password_reset_token && $user->password_reset_expires) {
            // Allow new token only after 5 minutes
            $minTimeBetweenRequests = now()->subMinutes(5);
            
            if ($user->password_reset_expires->isFuture() && 
                $user->updated_at->isAfter($minTimeBetweenRequests)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Send password reset email.
     *
     * @param \App\Models\User $user
     * @param string $token
     * @return void
     */
    protected function sendPasswordResetEmail($user, $token)
    {
        // Queue email for sending
        DB::table('email_queue')->insert([
            'to' => $user->email,
            'subject' => 'Password Reset Request - FirmwareHub',
            'template' => 'emails.password-reset',
            'data' => json_encode([
                'user_name' => $user->first_name,
                'reset_url' => route('password.reset', ['token' => $token]),
                'expires_in' => $this->tokenExpiryMinutes . ' minutes',
                'ip_address' => request()->ip(),
                'browser' => $this->getBrowserName(),
                'operating_system' => $this->getOperatingSystem()
            ]),
            'priority' => 'high',
            'created_at' => now()
        ]);
    }
    
    /**
     * Log password reset request.
     *
     * @param \App\Models\User $user
     * @return void
     */
    protected function logPasswordResetRequest($user)
    {
        DB::table('password_reset_logs')->insert([
            'user_id' => $user->id,
            'email' => $user->email,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'requested_at' => now()
        ]);
        
        // Clean old logs (keep only last 30 days)
        DB::table('password_reset_logs')
            ->where('requested_at', '<', now()->subDays(30))
            ->delete();
    }
    
    /**
     * Get browser name from user agent.
     *
     * @return string
     */
    protected function getBrowserName()
    {
        $userAgent = request()->userAgent();
        
        if (strpos($userAgent, 'Firefox') !== false) return 'Firefox';
        if (strpos($userAgent, 'Chrome') !== false) return 'Chrome';
        if (strpos($userAgent, 'Safari') !== false) return 'Safari';
        if (strpos($userAgent, 'Edge') !== false) return 'Edge';
        if (strpos($userAgent, 'Opera') !== false) return 'Opera';
        
        return 'Unknown Browser';
    }
    
    /**
     * Get operating system from user agent.
     *
     * @return string
     */
    protected function getOperatingSystem()
    {
        $userAgent = request()->userAgent();
        
        if (strpos($userAgent, 'Windows') !== false) return 'Windows';
        if (strpos($userAgent, 'Mac') !== false) return 'macOS';
        if (strpos($userAgent, 'Linux') !== false) return 'Linux';
        if (strpos($userAgent, 'Android') !== false) return 'Android';
        if (strpos($userAgent, 'iOS') !== false) return 'iOS';
        
        return 'Unknown OS';
    }
    
    /**
     * Check if has too many attempts.
     *
     * @param \Illuminate\Http\Request $request
     * @return bool
     */
    protected function hasTooManyAttempts(Request $request)
    {
        return RateLimiter::tooManyAttempts(
            $this->throttleKey($request),
            $this->maxAttempts
        );
    }
    
    /**
     * Increment attempts.
     *
     * @param \Illuminate\Http\Request $request
     * @return void
     */
    protected function incrementAttempts(Request $request)
    {
        RateLimiter::hit(
            $this->throttleKey($request),
            $this->decayMinutes * 60
        );
    }
    
    /**
     * Send lockout response.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function sendLockoutResponse(Request $request)
    {
        $seconds = RateLimiter::availableIn($this->throttleKey($request));
        $minutes = ceil($seconds / 60);
        
        Session::flash('error', "Too many password reset attempts. Please try again in {$minutes} minutes.");
        
        return back();
    }
    
    /**
     * Get throttle key.
     *
     * @param \Illuminate\Http\Request $request
     * @return string
     */
    protected function throttleKey(Request $request)
    {
        return 'password_reset_' . $request->input('email') . '|' . $request->ip();
    }
}