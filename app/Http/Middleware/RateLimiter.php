<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class RateLimiter
{
    /**
     * Handle an incoming request with file-based rate limiting.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @param int $maxAttempts
     * @param int $decayMinutes
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $maxAttempts = 60, $decayMinutes = 1)
    {
        $key = $this->resolveRequestSignature($request);
        
        // Get current attempts from file cache
        $attempts = $this->getAttempts($key);
        
        // Check if limit exceeded
        if ($attempts >= $maxAttempts) {
            $retryAfter = $this->getRetryAfter($key, $decayMinutes);
            return $this->buildResponse($request, $retryAfter);
        }
        
        // Increment attempts
        $this->incrementAttempts($key, $decayMinutes);
        
        // Add rate limit headers
        $response = $next($request);
        
        return $this->addHeaders(
            $response,
            $maxAttempts,
            $this->calculateRemainingAttempts($key, $maxAttempts)
        );
    }
    
    /**
     * Resolve request signature for rate limiting.
     *
     * @param \Illuminate\Http\Request $request
     * @return string
     */
    protected function resolveRequestSignature(Request $request)
    {
        // Use combination of route, IP, and user ID (if authenticated)
        $userId = session('user_id', 'guest');
        $route = $request->route() ? $request->route()->getName() : $request->path();
        
        return 'rate_limit:' . sha1($route . '|' . $request->ip() . '|' . $userId);
    }
    
    /**
     * Get number of attempts from cache.
     *
     * @param string $key
     * @return int
     */
    protected function getAttempts($key)
    {
        $data = Cache::get($key, ['attempts' => 0, 'reset_at' => now()->timestamp]);
        
        // Reset if decay time has passed
        if ($data['reset_at'] < now()->timestamp) {
            return 0;
        }
        
        return $data['attempts'];
    }
    
    /**
     * Increment attempts in cache.
     *
     * @param string $key
     * @param int $decayMinutes
     */
    protected function incrementAttempts($key, $decayMinutes)
    {
        $data = Cache::get($key, ['attempts' => 0, 'reset_at' => now()->timestamp]);
        
        // Reset if decay time has passed
        if ($data['reset_at'] < now()->timestamp) {
            $data = [
                'attempts' => 1,
                'reset_at' => now()->addMinutes($decayMinutes)->timestamp
            ];
        } else {
            $data['attempts']++;
        }
        
        Cache::put($key, $data, $decayMinutes * 60);
    }
    
    /**
     * Get retry after seconds.
     *
     * @param string $key
     * @param int $decayMinutes
     * @return int
     */
    protected function getRetryAfter($key, $decayMinutes)
    {
        $data = Cache::get($key, ['attempts' => 0, 'reset_at' => now()->timestamp]);
        
        return max(0, $data['reset_at'] - now()->timestamp);
    }
    
    /**
     * Calculate remaining attempts.
     *
     * @param string $key
     * @param int $maxAttempts
     * @return int
     */
    protected function calculateRemainingAttempts($key, $maxAttempts)
    {
        $attempts = $this->getAttempts($key);
        return max(0, $maxAttempts - $attempts);
    }
    
    /**
     * Build rate limit response.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $retryAfter
     * @return \Illuminate\Http\Response
     */
    protected function buildResponse(Request $request, $retryAfter)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Too many attempts. Please try again later.',
                'retry_after' => $retryAfter
            ], 429)->withHeaders([
                'Retry-After' => $retryAfter,
                'X-RateLimit-Limit' => 0,
                'X-RateLimit-Remaining' => 0
            ]);
        }
        
        return response()->view('errors.429', ['retryAfter' => $retryAfter], 429)
            ->withHeaders(['Retry-After' => $retryAfter]);
    }
    
    /**
     * Add rate limit headers to response.
     *
     * @param mixed $response
     * @param int $maxAttempts
     * @param int $remainingAttempts
     * @return mixed
     */
    protected function addHeaders($response, $maxAttempts, $remainingAttempts)
    {
        $response->headers->set('X-RateLimit-Limit', $maxAttempts);
        $response->headers->set('X-RateLimit-Remaining', $remainingAttempts);
        
        return $response;
    }
}