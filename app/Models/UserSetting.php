<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class UserSetting extends Model
{
    protected $fillable = [
        'user_id',
        'key',
        'value',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function scopeOfKey(Builder $query, string $key): Builder
    {
        return $query->where('key', $key);
    }

    public static function get(int $userId, string $key, mixed $default = null): mixed
    {
        $setting = static::where('user_id', $userId)->where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    public static function set(int $userId, string $key, mixed $value): static
    {
        return static::updateOrCreate(
            ['user_id' => $userId, 'key' => $key],
            ['value' => $value]
        );
    }
}
