<?php

namespace App\Helpers;

use App\Models\User;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;

class AuthHelper
{
    /**
     * Get the currently authenticated user.
     *
     * @return \App\Models\User|null
     */
    public static function user()
    {
        $userId = Session::get('user_id');
        
        if (!$userId) {
            return null;
        }
        
        // Cache user object for performance
        return Cache::remember('auth_user_' . $userId, 300, function () use ($userId) {
            return User::find($userId);
        });
    }
    
    /**
     * Check if user is authenticated.
     *
     * @return bool
     */
    public static function check()
    {
        return Session::has('user_id');
    }
    
    /**
     * Check if user is guest.
     *
     * @return bool
     */
    public static function guest()
    {
        return !self::check();
    }
    
    /**
     * Get authenticated user ID.
     *
     * @return int|null
     */
    public static function id()
    {
        return Session::get('user_id');
    }
    
    /**
     * Check if user has a specific role.
     *
     * @param string $role
     * @return bool
     */
    public static function hasRole($role)
    {
        $user = self::user();
        return $user ? $user->role === $role : false;
    }
    
    /**
     * Check if user is admin.
     *
     * @return bool
     */
    public static function isAdmin()
    {
        return self::hasRole('admin');
    }
    
    /**
     * Check if user has active subscription.
     *
     * @return bool
     */
    public static function hasSubscription()
    {
        // Check cached value first
        if (Session::has('has_subscription')) {
            return Session::get('has_subscription');
        }
        
        $user = self::user();
        if (!$user) {
            return false;
        }
        
        $hasSubscription = $user->hasActiveSubscription();
        Session::put('has_subscription', $hasSubscription);
        
        return $hasSubscription;
    }
    
    /**
     * Check if user email is verified.
     *
     * @return bool
     */
    public static function isVerified()
    {
        $user = self::user();
        return $user ? ($user->is_verified || $user->email_verified_at !== null) : false;
    }
    
    /**
     * Login user and create session.
     *
     * @param \App\Models\User $user
     * @param bool $remember
     * @return bool
     */
    public static function login($user, $remember = false)
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
            cookie()->queue('remember_token', $token, 43200); // 30 days
        }
        
        // Store session in cache
        Cache::put('user_session_' . $user->id, [
            'session_id' => Session::getId(),
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'last_activity' => now()
        ], 7200); // 2 hours
        
        // Update user login info
        $user->update([
            'last_login_at' => now(),
            'last_login_ip' => request()->ip(),
            'login_attempts' => 0
        ]);
        
        return true;
    }
    
    /**
     * Logout user and clear session.
     *
     * @return bool
     */
    public static function logout()
    {
        $userId = Session::get('user_id');
        
        if ($userId) {
            // Clear remember token
            User::where('id', $userId)->update(['remember_token' => null]);
            
            // Clear cached data
            Cache::forget('user_session_' . $userId);
            Cache::forget('auth_user_' . $userId);
        }
        
        // Clear session
        Session::flush();
        Session::regenerate();
        
        // Clear remember cookie
        cookie()->queue(cookie()->forget('remember_token'));
        
        return true;
    }
    
    /**
     * Attempt to authenticate user.
     *
     * @param array $credentials
     * @param bool $remember
     * @return bool
     */
    public static function attempt(array $credentials, $remember = false)
    {
        $user = User::where('email', strtolower($credentials['email']))->first();
        
        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return false;
        }
        
        if ($user->status !== 'active') {
            return false;
        }
        
        return self::login($user, $remember);
    }
}