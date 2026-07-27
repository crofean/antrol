<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class ThrottleLoginAttempts
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $key = 'login-attempts:' . $request->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => "Terlalu banyak percobaan login. Silakan coba lagi dalam {$seconds} detik."
                ], 429);
            }

            return redirect()->back()
                ->withInput($request->only('username'))
                ->withErrors(['auth_error' => "Terlalu banyak percobaan login. Silakan coba lagi dalam {$seconds} detik."]);
        }

        $response = $next($request);

        // If the session has authentication errors (meaning login failed), hit the limiter
        if (session()->has('errors') && session('errors')->has('auth_error')) {
            RateLimiter::hit($key, 60);
        } elseif (session()->has('auth_token')) {
            // Success, clear the rate limit
            RateLimiter::clear($key);
        }

        return $response;
    }
}
