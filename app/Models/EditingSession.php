<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EditingSession extends Model
{
    protected $fillable = [
        'user_id',
        'model',
        'record_id',
        'last_heartbeat',
    ];

    protected $casts = [
        'last_heartbeat' => 'datetime',
        'record_id' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isExpired(): bool
    {
        return $this->last_heartbeat->diffInSeconds(now()) > 120;
    }

    public static function cleanupStale(): int
    {
        return static::where('last_heartbeat', '<', now()->subMinutes(2))->delete();
    }
}
