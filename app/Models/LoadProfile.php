<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class LoadProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'titik_ukur_id',
        'recorded_at',
        'kwh_out',
        'kvarh_out',
        'kwh_in',
        'kvarh_in',
        'kv_r',
        'kv_s',
        'kv_t',
        'ir',
        'is',
        'it',
        'pf',
        'freq',
        'mw_out',
        'mvar_out',
    ];

    protected function casts(): array
    {
        return [
            'recorded_at' => 'datetime',
            'kwh_out' => 'float',
            'kvarh_out' => 'float',
            'kwh_in' => 'float',
            'kvarh_in' => 'float',
            'kv_r' => 'float',
            'kv_s' => 'float',
            'kv_t' => 'float',
            'ir' => 'float',
            'is' => 'float',
            'it' => 'float',
            'pf' => 'float',
            'freq' => 'float',
            'mw_out' => 'float',
            'mvar_out' => 'float',
        ];
    }

    public function titikUkur(): BelongsTo
    {
        return $this->belongsTo(TitikUkur::class);
    }

    public function scopeForTitikUkur(Builder $query, int $titikUkurId): Builder
    {
        return $query->where('titik_ukur_id', $titikUkurId);
    }

    public function scopeBetween(Builder $query, string $start, string $end): Builder
    {
        return $query->whereBetween('recorded_at', [$start, $end]);
    }
}