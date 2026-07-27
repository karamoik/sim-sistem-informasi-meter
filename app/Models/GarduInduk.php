<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class GarduInduk extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'region',
        'latitude',
        'longitude',
        'status',
    ];

    protected $appends = ['total_meters', 'online_count', 'offline_count', 'is_warning'];

    public function meters(): HasMany
    {
        return $this->hasMany(KwhMeter::class);
    }

    public function titikUkurs(): HasMany
    {
        return $this->hasMany(TitikUkur::class);
    }

    public function getTotalMetersAttribute(): int
    {
        return $this->meters()->count();
    }

    public function getOnlineCountAttribute(): int
    {
        return $this->meters()->where('status', 'online')->count();
    }

    public function getOfflineCountAttribute(): int
    {
        return $this->meters()->where('status', 'offline')->count();
    }

    public function getIsWarningAttribute(): bool
    {
        return $this->offline_count > 0;
    }
}
