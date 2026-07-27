<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class TitikUkur extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'gardu_induk_id',
        'latitude',
        'longitude',
        'address',
        'status',
    ];

    public function garduInduk(): BelongsTo
    {
        return $this->belongsTo(GarduInduk::class);
    }

    public function kwhMeters(): HasMany
    {
        return $this->hasMany(KwhMeter::class);
    }

    public function konfigurasiMeters(): HasMany
    {
        return $this->hasMany(KonfigurasiMeter::class);
    }

}
