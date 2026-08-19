<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;

#[Fillable(['name', 'email', 'password', 'role', 'is_active', 'two_factor_secret', 'two_factor_enabled', 'failed_login_attempts', 'locked_until'])]
#[Hidden(['password', 'remember_token', 'two_factor_secret'])]
class User extends Authenticatable implements JWTSubject
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    public const ROLES = ['admin', 'operator', 'viewer'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_enabled' => 'boolean',
            'is_active' => 'boolean',
            'locked_until' => 'datetime',
        ];
    }

    public function garduInduks(): BelongsToMany
    {
        return $this->belongsToMany(GarduInduk::class, 'user_gardu_induks')->withTimestamps();
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(UserSession::class);
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    public function settings(): HasMany
    {
        return $this->hasMany(UserSetting::class);
    }

    public function getSetting(string $key, mixed $default = null): mixed
    {
        return UserSetting::get($this->id, $key, $default);
    }

    public function setSetting(string $key, mixed $value): UserSetting
    {
        return UserSetting::set($this->id, $key, $value);
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isOperator(): bool
    {
        return $this->role === 'operator';
    }

    public function isViewer(): bool
    {
        return $this->role === 'viewer';
    }

    public function canWrite(): bool
    {
        return in_array($this->role, ['admin', 'operator']);
    }

    public function canManageUsers(): bool
    {
        return $this->role === 'admin';
    }

    public function getAssignedGiIds(): array
    {
        if ($this->role === 'admin') {
            return [];
        }
        return $this->garduInduks->pluck('id')->toArray();
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims(): array
    {
        return [
            'role' => $this->role,
            'is_active' => $this->is_active,
        ];
    }
}
