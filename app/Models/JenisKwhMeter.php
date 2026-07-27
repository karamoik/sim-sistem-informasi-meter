<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class JenisKwhMeter extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'phase',
        'max_capacity',
        'manufacturer',
        'description',
    ];

    protected $casts = [
        'max_capacity' => 'decimal:2',
    ];

    public function konfigurasiMeters(): HasMany
    {
        return $this->hasMany(KonfigurasiMeter::class);
    }

    public function kwhMeters(): HasMany
    {
        return $this->hasMany(KwhMeter::class);
    }
}
