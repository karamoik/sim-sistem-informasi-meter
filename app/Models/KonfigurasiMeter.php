<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class KonfigurasiMeter extends Model
{
    use HasFactory, SoftDeletes;

protected $fillable = [
        'titik_ukur_id',
        'kwh_meter_id',
        'jenis_kwh_meter_id',
        'period',
        'start_date',
        'end_date',
        'status',
        'notes',
        'document_path',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function kwhMeter(): BelongsTo
    {
        return $this->belongsTo(KwhMeter::class);
    }

    public function titikUkur(): BelongsTo
    {
        return $this->belongsTo(TitikUkur::class);
    }

    public function documentUrl(): ?string
    {
        if (!$this->document_path) {
            return null;
        }

        return \Illuminate\Support\Facades\Storage::disk('minio')->temporaryUrl(
            $this->document_path,
            now()->addHours(1)
        );
    }

    public function jenisKwhMeter(): BelongsTo
    {
        return $this->belongsTo(JenisKwhMeter::class);
    }
}
