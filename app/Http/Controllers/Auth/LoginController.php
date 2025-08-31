<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    /**
     * Maximum login attempts before lockout
     */
    protected $maxAttempts = 5;
    
    /**
     * Lockout time in minutes
     */
    protected $decayMinutes = 15;
    
    /**
     * Show the login form.
     *
     * @return \Illuminate\View\View
     */
    public function showLoginForm()
    {
        if ($this->isAuthenticated()) {
            return redirect()->route('account.dashboard');
        }
        
        return view('auth.login');
    }
    
    /**
     * Handle a login request.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function login(Request $request)
    {
        // Validate the form data
        $credentials = $this->validateLogin($request);
        
        // Check rate limiting
        if ($this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);
            return $this->sendLockoutResponse($request);
        }
        
        // Attempt to log the user in
        if ($this->attemptLogin($credentials)) {
            return $this->sendLoginResponse($request);
        }
        
        // Increment login attempts
        $this->incrementLoginAttempts($request);
        
        return $this->sendFailedLoginResponse($request);
    }
    
    /**
     * Validate the user login request.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    protected function validateLogin(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email|max:100',
            'password' => 'required|string|min:6',
            'remember' => 'boolean'
        ]);
        
        return $request->only('email', 'password', 'remember');
    }
    
    /**
     * Attempt to log the user into the application.
     *
     * @param array $credentials
     * @return bool
     */
    protected function attemptLogin($credentials)
    {
        // Find user by email
        $user = User::where('email', $credentials['email'])->first();
        
        if (!$user) {
            return false;
        }
        
        // Check if account is locked
        if ($user->locked_until && $user->locked_until->isFuture()) {
            throw ValidationException::withMessages([
                'email' => ['Your account is locked. Please try again later.'],
            ]);
        }
        
        // Check password
        if (!Hash::check($credentials['password'], $user->password)) {
            // Update failed login attempts
            $user->increment('login_attempts');
            
            // Lock account after too many attempts
            if ($user->login_attempts >= 10) {
                $user->update(['locked_until' => now()->addHours(1)]);
            }
            
            return false;
        }
        
        // Check if account is active
        if ($user->status !== 'active') {
            throw ValidationException::withMessages([
                'email' => ['Your account is not active. Please contact support.'],
            ]);
        }
        
        // Check if email is verified
        if (!$user->is_verified && !$user->email_verified_at) {
            throw ValidationException::withMessages([
                'email' => ['Please verify your email address before logging in.'],
            ]);
        }
        
        // Login successful - create session
        $this->createUserSession($user, $credentials['remember'] ?? false);
        
        // Reset login attempts
        $user->update([
            'login_attempts' => 0,
            'last_login_at' => now(),
            'last_login_ip' => request()->ip()
        ]);
        
        return true;
    }
    
    /**
     * Create a new session for the user.
     *
     * @param \App\Models\User $user
     * @param bool $remember
     * @return void
     */
    protected function createUserSession($user, $remember = false)
    {
        // Regenerate session ID for security
        Session::regenerate();
        
        // Store user data in session
        Session::put('user_id', $user->id);
        Session::put('user_email', $user->email);
        Session::put('user_name', $user->full_name);
        Session::put('user_role', $user->role);
        Session::put('logged_in_at', now()->timestamp);
        
        // Set remember token if requested
        if ($remember) {
            $token = bin2hex(random_bytes(32));
            $user->update(['remember_token' => $token]);
            
            // Set cookie for 30 days
            cookie()->queue('remember_token', $token, 43200);
        }
        
        // Store session in cache for shared hosting performance
        $sessionKey = 'user_session_' . $user->id;
        Cache::put($sessionKey, [
            'session_id' => Session::getId(),
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'last_activity' => now()
        ], 120); // 2 hours
    }
    
    /**
     * Send the response after the user was authenticated.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function sendLoginResponse(Request $request)
    {
        $this->clearLoginAttempts($request);
        
        // Check if user has active subscription
        $user = User::find(Session::get('user_id'));
        if ($user->hasActiveSubscription()) {
            Session::put('has_subscription', true);
        }
        
        // Flash success message
        Session::flash('success', 'Welcome back, ' . $user->first_name . '!');
        
        // Redirect to intended URL or dashboard
        return redirect()->intended(route('account.dashboard'));
    }
    
    /**
     * Send failed login response.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function sendFailedLoginResponse(Request $request)
    {
        throw ValidationException::withMessages([
            'email' => ['Invalid email or password.'],
        ]);
    }
    
    /**
     * Log the user out of the application.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function logout(Request $request)
    {
        // Get user before clearing session
        $userId = Session::get('user_id');
        
        if ($userId) {
            // Clear remember token
            User::where('id', $userId)->update(['remember_token' => null]);
            
            // Clear cached session
            Cache::forget('user_session_' . $userId);
        }
        
        // Clear session
        Session::flush();
        Session::regenerate();
        
        // Clear remember cookie
        cookie()->queue(cookie()->forget('remember_token'));
        
        // Flash message
        Session::flash('success', 'You have been logged out successfully.');
        
        return redirect()->route('login');
    }
    
    /**
     * Determine if the user has too many failed login attempts.
     *
     * @param \Illuminate\Http\Request $request
     * @return bool
     */
    protected function hasTooManyLoginAttempts(Request $request)
    {
        return RateLimiter::tooManyAttempts(
            $this->throttleKey($request),
            $this->maxAttempts
        );
    }
    
    /**
     * Increment the login attempts for the user.
     *
     * @param \Illuminate\Http\Request $request
     * @return void
     */
    protected function incrementLoginAttempts(Request $request)
    {
        RateLimiter::hit(
            $this->throttleKey($request),
            $this->decayMinutes * 60
        );
    }
    
    /**
     * Clear the login locks for the given user credentials.
     *
     * @param \Illuminate\Http\Request $request
     * @return void
     */
    protected function clearLoginAttempts(Request $request)
    {
        RateLimiter::clear($this->throttleKey($request));
    }
    
    /**
     * Fire an event when a lockout occurs.
     *
     * @param \Illuminate\Http\Request $request
     * @return void
     */
    protected function fireLockoutEvent(Request $request)
    {
        // Log lockout event
        error_log('Login lockout for IP: ' . $request->ip() . ' Email: ' . $request->input('email'));
    }
    
    /**
     * Redirect the user after determining they are locked out.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function sendLockoutResponse(Request $request)
    {
        $seconds = RateLimiter::availableIn($this->throttleKey($request));
        $minutes = ceil($seconds / 60);
        
        throw ValidationException::withMessages([
            'email' => ["Too many login attempts. Please try again in {$minutes} minutes."],
        ]);
    }
    
    /**
     * Get the throttle key for the given request.
     *
     * @param \Illuminate\Http\Request $request
     * @return string
     */
    protected function throttleKey(Request $request)
    {
        return strtolower($request->input('email')) . '|' . $request->ip();
    }
    
    /**
     * Check if user is already authenticated.
     *
     * @return bool
     */
    protected function isAuthenticated()
    {
        // Check session
        if (Session::has('user_id')) {
            // Validate session hasn't expired (2 hours)
            $loggedInAt = Session::get('logged_in_at');
            if ($loggedInAt && (time() - $loggedInAt) < 7200) {
                return true;
            }
        }
        
        // Check remember token
        $rememberToken = request()->cookie('remember_token');
        if ($rememberToken) {
            $user = User::where('remember_token', $rememberToken)
                ->where('status', 'active')
                ->first();
            
            if ($user) {
                $this->createUserSession($user, true);
                return true;
            }
        }
        
        return false;
    }
}