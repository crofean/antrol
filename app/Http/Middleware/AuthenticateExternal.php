<?php

namespace App\Http\Middleware;

use App\Services\ExternalAuthService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Cache;

class AuthenticateExternal
{
    protected $authService;

    public function __construct(ExternalAuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Check Web Session
        if (session()->has('auth_token')) {
            View::share('authUser', session('auth_user'));
            return $next($request);
        }

        // 2. Check Token in Authorization (Bearer) or X-API-TOKEN Header
        $token = $request->bearerToken() ?: $request->header('X-API-TOKEN');

        if ($token) {
            $cacheKey = 'api_token_user:' . md5($token);
            $userInfo = Cache::remember($cacheKey, 300, function () use ($token) {
                return $this->authService->getUserInfo($token);
            });

            if (isset($userInfo['success']) && $userInfo['success']) {
                session(['auth_token' => $token, 'auth_user' => $userInfo['user']]);
                View::share('authUser', $userInfo['user']);
                return $next($request);
            }
        }

        // 3. Unauthenticated handling
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => false,
                'message' => 'Sesi Anda telah berakhir atau belum terautentikasi. Silakan login kembali.'
            ], 401);
        }

        return redirect()->route('login');
    }
}
