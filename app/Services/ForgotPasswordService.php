<?php

namespace App\Services;

use App\Models\PasswordReset;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ForgotPasswordService
{
    public function createToken(string $email): ?string
    {
        $user = User::where('email', $email)->first();

        if (!$user) {
            return null;
        }

        PasswordReset::where('email', $email)->delete();

        $token = Str::random(64);

        PasswordReset::create([
            'email'      => $email,
            'token'      => Hash::make($token),
            'created_at' => now(),
        ]);

        return $token;
    }

    public function validateToken(string $email, string $token): bool
    {
        $reset = PasswordReset::where('email', $email)->first();

        if (!$reset) {
            return false;
        }

        if ($reset->created_at->lt(now()->subHours(6))) {
            $reset->delete();
            return false;
        }

        return Hash::check($token, $reset->token);
    }

    public function resetPassword(string $email, string $token, string $password): bool
    {
        if (!$this->validateToken($email, $token)) {
            return false;
        }

        $user = User::where('email', $email)->first();

        if (!$user) {
            return false;
        }

        $user->update(['password' => $password]);

        PasswordReset::where('email', $email)->delete();

        return true;
    }
}
