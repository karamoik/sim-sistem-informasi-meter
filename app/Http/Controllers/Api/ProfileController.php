<?php

namespace App\Http\Controllers\Api;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ProfileController extends BaseApiController
{
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();
        return $this->success([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'is_active' => $user->is_active,
            'created_at' => $user->created_at->toISOString(),
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email,' . $request->user()->id],
        ]);

        $user = $request->user();
        $old = ['name' => $user->name, 'email' => $user->email];
        $user->update($validated);

        ActivityLog::log('profile_updated', 'Profil diperbarui', User::class, $user->id, $old, $validated);

        return $this->success([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
        ], 'Profil berhasil diperbarui');
    }

    public function changePassword(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'confirmed', Password::min(8)->mixedCase()->numbers()],
        ]);

        $user = $request->user();

        if (!Hash::check($validated['current_password'], $user->password)) {
            return $this->error('Password lama salah.', null, 422);
        }

        $user->update(['password' => Hash::make($validated['password'])]);

        ActivityLog::log('password_changed', 'Password berhasil diubah', User::class, $user->id);

        return $this->success(null, 'Password berhasil diubah');
    }
}
