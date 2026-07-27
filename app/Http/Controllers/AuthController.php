<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Services\ExternalAuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuthController extends Controller
{
    protected $authService;

    public function __construct(ExternalAuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Show the login form
     */
    public function showLoginForm(): View|RedirectResponse
    {
        if (session()->has('auth_token')) {
            return redirect('/');
        }

        return view('auth.login');
    }

    /**
     * Process external SSO login
     */
    public function login(LoginRequest $request): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $result = $this->authService->login(
            $request->input('username'),
            $request->input('password')
        );

        if (!$result['success']) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'],
                ], 401);
            }

            return redirect()->back()
                ->withInput($request->only('username'))
                ->withErrors(['auth_error' => $result['message']]);
        }

        // Fetch detailed user information using the token
        $token = $result['token'];
        $userInfo = $this->authService->getUserInfo($token);

        if (!$userInfo['success']) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => $userInfo['message'],
                ], 401);
            }

            return redirect()->back()
                ->withInput($request->only('username'))
                ->withErrors(['auth_error' => $userInfo['message']]);
        }

        // Store SSO session info in Laravel session
        session([
            'auth_token' => $token,
            'auth_user' => $userInfo['user'],
        ]);

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => true,
                'message' => 'Login berhasil.',
                'token' => $token,
                'user' => $userInfo['user'],
            ]);
        }

        return redirect()->intended('/');
    }

    /**
     * Process logout
     */
    public function logout(Request $request): RedirectResponse
    {
        $token = session('auth_token');

        if ($token) {
            $this->authService->logout($token);
        }

        $request->session()->forget(['auth_token', 'auth_user']);
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Anda telah berhasil logout.');
    }
}
