<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class ExternalAuthService
{
    protected $apiUrl;

    public function __construct()
    {
        $this->apiUrl = rtrim(config('external_auth.api_url'), '/');
    }

    /**
     * Authenticate user with username and password
     *
     * @param string $username
     * @param string $password
     * @return array
     */
    public function login(string $username, string $password): array
    {
        try {
            Log::info('Attempting external SSO login', ['username' => $username]);

            $response = Http::timeout(15)
                ->connectTimeout(10)
                ->post("{$this->apiUrl}/api/core/sign", [
                    'username' => $username,
                    'password' => $password,
                ]);

            if ($response->failed()) {
                Log::warning('External SSO login HTTP request failed', [
                    'status' => $response->status(),
                    'username' => $username,
                ]);

                $errorData = $response->json();
                if (is_array($errorData) && isset($errorData['message'])) {
                    return [
                        'success' => false,
                        'message' => $errorData['message'],
                    ];
                }

                return [
                    'success' => false,
                    'message' => 'Gagal terhubung ke server autentikasi external (' . $response->status() . ').',
                ];
            }

            $data = $response->json();

            if (isset($data['statusCode']) && in_array((int)$data['statusCode'], [0, 200], true) && isset($data['resource']['accessToken'])) {
                return [
                    'success' => true,
                    'token' => $data['resource']['accessToken'],
                    'message' => $data['message'] ?? 'Login berhasil.',
                ];
            }

            Log::warning('External SSO login rejected credentials or bad response structure', [
                'username' => $username,
                'response' => $data,
            ]);

            return [
                'success' => false,
                'message' => $data['message'] ?? 'Username atau password salah.',
            ];
        } catch (Exception $e) {
            Log::error('Error occurred during external SSO login', [
                'username' => $username,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Terjadi kesalahan sistem saat menghubungi server autentikasi.',
            ];
        }
    }

    /**
     * Fetch user profile information using the access token
     *
     * @param string $token
     * @return array
     */
    public function getUserInfo(string $token): array
    {
        try {
            $response = Http::timeout(10)
                ->connectTimeout(5)
                ->withHeaders([
                    'X-API-TOKEN' => $token
                ])
                ->get("{$this->apiUrl}/api/core/auth/info");

            if ($response->failed()) {
                Log::warning('Failed to fetch user info from external SSO', [
                    'status' => $response->status(),
                ]);
                return [
                    'success' => false,
                    'message' => 'Gagal mengambil informasi user dari server.',
                ];
            }

            $data = $response->json();

            if (isset($data['statusCode']) && in_array((int)$data['statusCode'], [0, 200], true) && isset($data['resource'])) {
                return [
                    'success' => true,
                    'user' => $data['resource'],
                ];
            }

            return [
                'success' => false,
                'message' => $data['message'] ?? 'Informasi user tidak valid.',
            ];
        } catch (Exception $e) {
            Log::error('Error fetching user info from external SSO', [
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Terjadi kesalahan sistem saat menghubungi server autentikasi.',
            ];
        }
    }

    /**
     * Terminate the SSO session
     *
     * @param string $token
     * @return bool
     */
    public function logout(string $token): bool
    {
        try {
            $response = Http::timeout(10)
                ->connectTimeout(5)
                ->withHeaders([
                    'X-API-TOKEN' => $token
                ])
                ->post("{$this->apiUrl}/api/core/logout");

            if ($response->failed()) {
                Log::warning('Logout request to external SSO failed', [
                    'status' => $response->status(),
                ]);
                return false;
            }

            $data = $response->json();
            return isset($data['statusCode']) && in_array((int)$data['statusCode'], [0, 200], true);
        } catch (Exception $e) {
            Log::error('Error occurred during external SSO logout', [
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }
}
