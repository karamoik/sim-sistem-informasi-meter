<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Services\AuthService;

class AuthController extends BaseApiController
{
    public function __construct(
        protected AuthService $authService
    ) {}

    public function login(LoginRequest $request)
    {
        if (!$request->validateCaptcha()) {
            return $this->error('Jawaban captcha salah atau sudah kedaluwarsa.', null, 422);
        }

        $credentials = collect($request->validated())
            ->only(['email', 'password'])
            ->toArray();

        $validated = $request->validated();

        $deviceInfo = $validated['device_info'] ?? [];
        $deviceInfo['fingerprint_id'] = $validated['fingerprint_id'] ?? null;
        $deviceInfo['user_agent'] = $request->userAgent();
        $deviceInfo['ip_address'] = $request->ip();

        return $this->success(
            $this->authService->login($credentials, $deviceInfo),
            'Login berhasil'
        );
    }

    public function register(RegisterRequest $request)
    {
        return $this->success(
            $this->authService->register(
                $request->validated()
            ),
            'Register berhasil',
            201
        );
    }

    public function logout()
    {
        $this->authService->logout();

        return $this->success(null, 'Logout berhasil');
    }

    public function refresh()
    {
        return $this->success(
            $this->authService->refresh(),
            'Token refreshed'
        );
    }

    public function me()
    {
        return $this->success(
            $this->authService->me(),
            'User profile'
        );
    }
}
