<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class KwhMeter extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'meter_no',
        'gardu_induk_id',
        'titik_ukur_id',
        'jenis_kwh_meter_id',
        'unit_name',
        'location',
        'last_reading',
        'last_update',
        'status',
    ];

    protected $casts = [
        'last_reading' => 'decimal:2',
        'last_update' => 'datetime',
    ];

    public function garduInduk(): BelongsTo
    {
        return $this->belongsTo(GarduInduk::class);
    }

    public function titikUkur(): BelongsTo
    {
        return $this->belongsTo(TitikUkur::class);
    }

    public function jenisKwhMeter(): BelongsTo
    {
        return $this->belongsTo(JenisKwhMeter::class);
    }
}
