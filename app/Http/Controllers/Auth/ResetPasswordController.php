<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rules\Password;

class ResetPasswordController extends Controller
{
    /**
     * Maximum reset attempts
     */
    protected $maxAttempts = 3;
    
    /**
     * Decay time in minutes
     */
    protected $decayMinutes = 60;
    
    /**
     * Show the password reset form.
     *
     * @param \Illuminate\Http\Request $request
     * @param string $token
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function showResetForm(Request $request, $token)
    {
        // Validate token format
        if (!$token || strlen($token) !== 64) {
            Session::flash('error', 'Invalid password reset link.');
            return redirect()->route('password.request');
        }
        
        // Check if token exists in cache
        $userId = Cache::get('pwd_reset_' . $token);
        
        if (!$userId) {
            // Check database as fallback
            $user = User::where('password_reset_token', $token)
                       ->where('password_reset_expires', '>', now())
                       ->first();
            
            if (!$user) {
                Session::flash('error', 'Invalid or expired password reset link.');
                return redirect()->route('password.request');
            }
            
            $userId = $user->id;
        }
        
        // Get user
        $user = User::find($userId);
        
        if (!$user) {
            Session::flash('error', 'Invalid password reset link.');
            return redirect()->route('password.request');
        }
        
        // Check if token is expired
        if ($user->password_reset_expires && $user->password_reset_expires->isPast()) {
            $this->clearResetToken($user);
            Session::flash('error', 'Password reset link has expired. Please request a new one.');
            return redirect()->route('password.request');
        }
        
        return view('auth.passwords.reset', [
            'token' => $token,
            'email' => $user->email
        ]);
    }
    
    /**
     * Reset the given user's password.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function reset(Request $request)
    {
        // Validate the request
        $validated = $this->validateReset($request);
        
        // Check rate limiting
        if ($this->hasTooManyAttempts($request)) {
            return $this->sendLockoutResponse($request);
        }
        
        // Validate token
        $user = $this->validateToken($validated['token']);
        
        if (!$user) {
            Session::flash('error', 'Invalid or expired password reset link.');
            return redirect()->route('password.request');
        }
        
        // Verify email matches
        if (strtolower($user->email) !== strtolower($validated['email'])) {
            Session::flash('error', 'Email address does not match our records.');
            return back()->withInput($request->only('email'));
        }
        
        // Check if new password is same as old
        if (Hash::check($validated['password'], $user->password)) {
            Session::flash('error', 'New password cannot be the same as your current password.');
            return back()->withInput($request->only('email'));
        }
        
        // Check password history (optional - for enhanced security)
        if ($this->isPasswordReused($user, $validated['password'])) {
            Session::flash('error', 'You cannot reuse a recently used password.');
            return back()->withInput($request->only('email'));
        }
        
        // Reset the password
        $this->resetPassword($user, $validated['password']);
        
        // Send confirmation email
        $this->sendPasswordResetConfirmation($user);
        
        // Clear rate limiting
        $this->clearAttempts($request);
        
        Session::flash('success', 'Your password has been reset successfully! Please login with your new password.');
        
        return redirect()->route('login');
    }
    
    /**
     * Validate the password reset request.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    protected function validateReset(Request $request)
    {
        return $request->validate([
            'token' => 'required|string|size:64',
            'email' => 'required|email|max:100',
            'password' => [
                'required',
                'confirmed',
                Password::min(8)
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
                    ->uncompromised()
            ]
        ], [
            'password.uncompromised' => 'This password has been compromised in a data breach. Please choose a different password.'
        ]);
    }
    
    /**
     * Validate the reset token.
     *
     * @param string $token
     * @return \App\Models\User|null
     */
    protected function validateToken($token)
    {
        // Check cache first
        $userId = Cache::get('pwd_reset_' . $token);
        
        if ($userId) {
            $user = User::find($userId);
            
            // Verify token matches and not expired
            if ($user && 
                $user->password_reset_token === $token &&
                $user->password_reset_expires &&
                $user->password_reset_expires->isFuture()) {
                return $user;
            }
        }
        
        // Fallback to database
        return User::where('password_reset_token', $token)
                   ->where('password_reset_expires', '>', now())
                   ->first();
    }
    
    /**
     * Reset the user's password.
     *
     * @param \App\Models\User $user
     * @param string $password
     * @return void
     */
    protected function resetPassword($user, $password)
    {
        // Update password and clear reset token
        $user->update([
            'password' => Hash::make($password),
            'password_reset_token' => null,
            'password_reset_expires' => null,
            'login_attempts' => 0,
            'locked_until' => null
        ]);
        
        // Clear cache
        Cache::forget('pwd_reset_' . $user->password_reset_token);
        Cache::forget('pwd_reset_meta_' . $user->id);
        
        // Store password history (for reuse check)
        $this->storePasswordHistory($user, $password);
        
        // Log password reset
        $this->logPasswordReset($user);
        
        // Invalidate all sessions for security
        $this->invalidateUserSessions($user);
    }
    
    /**
     * Check if password was recently used.
     *
     * @param \App\Models\User $user
     * @param string $password
     * @return bool
     */
    protected function isPasswordReused($user, $password)
    {
        // Check last 5 passwords
        $passwordHistory = DB::table('password_history')
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->pluck('password_hash');
        
        foreach ($passwordHistory as $oldPasswordHash) {
            if (Hash::check($password, $oldPasswordHash)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Store password in history.
     *
     * @param \App\Models\User $user
     * @param string $password
     * @return void
     */
    protected function storePasswordHistory($user, $password)
    {
        DB::table('password_history')->insert([
            'user_id' => $user->id,
            'password_hash' => Hash::make($password),
            'created_at' => now()
        ]);
        
        // Keep only last 10 passwords
        $keepIds = DB::table('password_history')
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->pluck('id');
        
        DB::table('password_history')
            ->where('user_id', $user->id)
            ->whereNotIn('id', $keepIds)
            ->delete();
    }
    
    /**
     * Log the password reset.
     *
     * @param \App\Models\User $user
     * @return void
     */
    protected function logPasswordReset($user)
    {
        DB::table('password_reset_logs')->insert([
            'user_id' => $user->id,
            'email' => $user->email,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'reset_at' => now()
        ]);
        
        DB::table('activity_logs')->insert([
            'user_id' => $user->id,
            'action' => 'password_reset',
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'created_at' => now()
        ]);
    }
    
    /**
     * Invalidate all user sessions.
     *
     * @param \App\Models\User $user
     * @return void
     */
    protected function invalidateUserSessions($user)
    {
        // Clear cached sessions
        Cache::forget('user_session_' . $user->id);
        
        // Clear remember token
        $user->update(['remember_token' => null]);
    }
    
    /**
     * Send password reset confirmation email.
     *
     * @param \App\Models\User $user
     * @return void
     */
    protected function sendPasswordResetConfirmation($user)
    {
        DB::table('email_queue')->insert([
            'to' => $user->email,
            'subject' => 'Password Reset Successful - FirmwareHub',
            'template' => 'emails.password-reset-success',
            'data' => json_encode([
                'user_name' => $user->first_name,
                'reset_time' => now()->format('F j, Y at g:i A'),
                'ip_address' => request()->ip(),
                'browser' => $this->getBrowserName(),
                'support_url' => route('contact')
            ]),
            'priority' => 'high',
            'created_at' => now()
        ]);
    }
    
    /**
     * Clear reset token.
     *
     * @param \App\Models\User $user
     * @return void
     */
    protected function clearResetToken($user)
    {
        if ($user->password_reset_token) {
            Cache::forget('pwd_reset_' . $user->password_reset_token);
        }
        
        Cache::forget('pwd_reset_meta_' . $user->id);
        
        $user->update([
            'password_reset_token' => null,
            'password_reset_expires' => null
        ]);
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
     * Clear attempts.
     *
     * @param \Illuminate\Http\Request $request
     * @return void
     */
    protected function clearAttempts(Request $request)
    {
        RateLimiter::clear($this->throttleKey($request));
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
        
        return back()->withInput($request->only('email'));
    }
    
    /**
     * Get throttle key.
     *
     * @param \Illuminate\Http\Request $request
     * @return string
     */
    protected function throttleKey(Request $request)
    {
        return 'password_reset_submit_' . $request->input('email') . '|' . $request->ip();
    }
}