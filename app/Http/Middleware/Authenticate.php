<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Cache;
use App\Models\User;

class Authenticate
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @param string|null $guard
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $guard = null)
    {
        // Check if user is authenticated
        if (!$this->check($request)) {
            // Store intended URL for redirect after login
            Session::put('url.intended', $request->url());
            
            // Return JSON response for API requests
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }
            
            // Redirect to login page
            Session::flash('error', 'Please login to continue.');
            return redirect()->route('login');
        }
        
        // Check if session has expired (2 hour timeout)
        if ($this->sessionExpired()) {
            $this->logout();
            Session::flash('error', 'Your session has expired. Please login again.');
            return redirect()->route('login');
        }
        
        // Update last activity timestamp
        $this->updateActivity();
        
        // Check if user account is still active
        $user = $this->getUser();
        if ($user && $user->status !== 'active') {
            $this->logout();
            Session::flash('error', 'Your account has been suspended.');
            return redirect()->route('login');
        }
        
        return $next($request);
    }
    
    /**
     * Check if user is authenticated.
     *
     * @param \Illuminate\Http\Request $request
     * @return bool
     */
    protected function check(Request $request)
    {
        // Check session
        if (Session::has('user_id')) {
            return true;
        }
        
        // Check remember token cookie
        $rememberToken = $request->cookie('remember_token');
        if ($rememberToken) {
            $user = User::where('remember_token', $rememberToken)
                       ->where('status', 'active')
                       ->first();
            
            if ($user) {
                $this->createSession($user);
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Check if session has expired.
     *
     * @return bool
     */
    protected function sessionExpired()
    {
        $loggedInAt = Session::get('logged_in_at');
        if (!$loggedInAt) {
            return true;
        }
        
        // 2 hour session timeout
        return (time() - $loggedInAt) > 7200;
    }
    
    /**
     * Update last activity timestamp.
     */
    protected function updateActivity()
    {
        $userId = Session::get('user_id');
        if ($userId) {
            // Update cache-based session activity
            $sessionKey = 'user_session_' . $userId;
            $sessionData = Cache::get($sessionKey, []);
            $sessionData['last_activity'] = now();
            Cache::put($sessionKey, $sessionData, 120); // 2 hours
        }
    }
    
    /**
     * Get authenticated user.
     *
     * @return \App\Models\User|null
     */
    protected function getUser()
    {
        $userId = Session::get('user_id');
        return $userId ? User::find($userId) : null;
    }
    
    /**
     * Create user session.
     *
     * @param \App\Models\User $user
     */
    protected function createSession($user)
    {
        Session::put('user_id', $user->id);
        Session::put('user_email', $user->email);
        Session::put('user_name', $user->full_name);
        Session::put('user_role', $user->role);
        Session::put('logged_in_at', time());
    }
    
    /**
     * Logout user.
     */
    protected function logout()
    {
        $userId = Session::get('user_id');
        if ($userId) {
            Cache::forget('user_session_' . $userId);
        }
        Session::flush();
    }
}