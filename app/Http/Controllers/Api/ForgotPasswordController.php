<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseApiController;
use App\Services\ForgotPasswordService;
use Illuminate\Http\Request;

class ForgotPasswordController extends BaseApiController
{
    public function __construct(
        protected ForgotPasswordService $forgotPasswordService
    ) {}

    public function sendResetLink(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $token = $this->forgotPasswordService->createToken($request->email);

        if ($token) {
            $resetLink = url("/reset-password?email=" . urlencode($request->email) . "&token=" . $token);

            return $this->success([
                'reset_link' => $resetLink,
                'token'      => $token,
            ], 'Link reset password berhasil dikirim.');
        }

        return $this->error('Email tidak ditemukan.', null, 404);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'email'    => ['required', 'email'],
            'token'    => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $success = $this->forgotPasswordService->resetPassword(
            $request->email,
            $request->token,
            $request->password
        );

        if ($success) {
            return $this->success(null, 'Password berhasil direset. Silakan login dengan password baru.');
        }

        return $this->error('Token tidak valid atau sudah kedaluwarsa.', null, 422);
    }
}
