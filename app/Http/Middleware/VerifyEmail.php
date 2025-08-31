<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Models\User;

class VerifyEmail
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Get authenticated user
        $userId = Session::get('user_id');
        
        if (!$userId) {
            return redirect()->route('login');
        }
        
        $user = User::find($userId);
        
        if (!$user) {
            Session::flush();
            return redirect()->route('login');
        }
        
        // Check if email is verified
        if (!$user->is_verified && !$user->email_verified_at) {
            // Allow access to verification routes
            if ($this->isVerificationRoute($request)) {
                return $next($request);
            }
            
            // Allow logout
            if ($request->routeIs('logout')) {
                return $next($request);
            }
            
            // Store intended URL
            Session::put('url.intended', $request->url());
            
            // Redirect to verification notice
            Session::flash('warning', 'Please verify your email address to continue.');
            return redirect()->route('verification.notice');
        }
        
        return $next($request);
    }
    
    /**
     * Check if current route is verification related.
     *
     * @param \Illuminate\Http\Request $request
     * @return bool
     */
    protected function isVerificationRoute(Request $request)
    {
        $verificationRoutes = [
            'verification.notice',
            'verification.verify',
            'verification.resend'
        ];
        
        foreach ($verificationRoutes as $route) {
            if ($request->routeIs($route)) {
                return true;
            }
        }
        
        return false;
    }
}