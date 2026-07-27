<?php

namespace App\Http\Controllers\Api;

use App\Events\CrudUpdated;
use App\Models\GarduInduk;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UserController extends BaseApiController
{
    public function index(Request $request): JsonResponse
    {
        $query = User::with('garduInduks');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%");
            });
        }

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $users = $query->orderBy('name')->paginate(20);

        return $this->success($users);
    }

    public function show(User $user): JsonResponse
    {
        $user->load('garduInduks');

        return $this->success($user);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => ['required', 'string', 'min:8', Password::min(8)->mixedCase()->numbers()],
            'role'     => 'required|in:admin,operator,viewer',
            'is_active' => 'sometimes|boolean',
            'gardu_induk_ids' => 'sometimes|array',
            'gardu_induk_ids.*' => 'exists:gardu_induks,id',
        ]);

        $password = $validated['password'];
        unset($validated['password']);
        $validated['password'] = Hash::make($password);

        $garduIndukIds = $validated['gardu_induk_ids'] ?? [];
        unset($validated['gardu_induk_ids']);

        $validated['is_active'] = $request->boolean('is_active', true);

        $user = User::create($validated);
        $user->garduInduks()->sync($garduIndukIds);

        broadcast(new CrudUpdated('user', 'created', $user->id, $user->name));

        return $this->success($user->load('garduInduks'), 'User created', 201);
    }

    public function update(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate([
            'name'     => 'sometimes|string|max:255',
            'email'    => 'sometimes|email|unique:users,email,' . $user->id,
            'password' => ['sometimes', 'nullable', 'string', 'min:8', Password::min(8)->mixedCase()->numbers()],
            'role'     => 'sometimes|in:admin,operator,viewer',
            'is_active' => 'sometimes|boolean',
            'gardu_induk_ids' => 'sometimes|array',
            'gardu_induk_ids.*' => 'exists:gardu_induks,id',
        ]);

        if (isset($validated['password']) && $validated['password'] !== null) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        if (array_key_exists('is_active', $validated)) {
            $validated['is_active'] = $request->boolean('is_active');
        }

        if (array_key_exists('gardu_induk_ids', $validated)) {
            $user->garduInduks()->sync($validated['gardu_induk_ids']);
            unset($validated['gardu_induk_ids']);
        }

        $user->update($validated);

        broadcast(new CrudUpdated('user', 'updated', $user->id, $user->name));

        return $this->success($user->load('garduInduks'), 'User updated');
    }

    public function destroy(User $user): JsonResponse
    {
        if ($user->id === auth('api')->id()) {
            return $this->error('Tidak bisa menghapus akun sendiri.', null, 400);
        }

        $name = $user->name;
        $user->delete();

        broadcast(new CrudUpdated('user', 'deleted', null, $name));

        return $this->success(null, 'User deleted');
    }

    public function assignUnits(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate([
            'gardu_induk_ids' => 'required|array',
            'gardu_induk_ids.*' => 'exists:gardu_induks,id',
        ]);

        $user->garduInduks()->sync($validated['gardu_induk_ids']);

        broadcast(new CrudUpdated('user', 'updated', $user->id, $user->name));

        return $this->success($user->load('garduInduks'), 'Units assigned');
    }

    public function availableUnits(): JsonResponse
    {
        $gi = GarduInduk::where('status', 'active')->orderBy('name')->get();

        return $this->success($gi);
    }
}
