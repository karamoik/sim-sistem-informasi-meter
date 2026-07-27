<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class UserSession extends Model
{
    protected $fillable = [
        'user_id',
        'fingerprint_id',
        'device_name',
        'device_info',
        'ip_address',
        'user_agent',
        'is_main',
        'is_active',
        'last_active_at',
    ];

    protected function casts(): array
    {
        return [
            'device_info' => 'array',
            'is_main' => 'boolean',
            'is_active' => 'boolean',
            'last_active_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function isExpired(): bool
    {
        return $this->last_active_at && $this->last_active_at->diffInMinutes(now()) > 60;
    }

    public function getBrowserAttribute(): string
    {
        $ua = strtolower($this->user_agent ?? '');
        if (str_contains($ua, 'edg/')) return 'Edge';
        if (str_contains($ua, 'opr/') || str_contains($ua, 'opera')) return 'Opera';
        if (str_contains($ua, 'chrome')) return 'Chrome';
        if (str_contains($ua, 'firefox')) return 'Firefox';
        if (str_contains($ua, 'safari')) return 'Safari';
        return 'Unknown';
    }

    public function getOsAttribute(): string
    {
        $ua = strtolower($this->user_agent ?? '');
        if (str_contains($ua, 'windows')) return 'Windows';
        if (str_contains($ua, 'mac os')) return 'macOS';
        if (str_contains($ua, 'linux')) return 'Linux';
        if (str_contains($ua, 'android')) return 'Android';
        if (str_contains($ua, 'iphone') || str_contains($ua, 'ipad')) return 'iOS';
        return 'Unknown';
    }

    public function getDeviceTypeAttribute(): string
    {
        $ua = strtolower($this->user_agent ?? '');
        if (preg_match('/tablet|ipad/i', $ua)) return 'tablet';
        if (preg_match('/mobile|phone|android|iphone/i', $ua)) return 'mobile';
        return 'desktop';
    }
}
