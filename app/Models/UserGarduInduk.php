<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class UserGarduInduk extends \Illuminate\Database\Eloquent\Model
{
    protected $fillable = ['user_id', 'gardu_induk_id'];

    public function user(): BelongsToMany
    {
        return $this->belongsTo(User::class);
    }

    public function garduInduk(): BelongsToMany
    {
        return $this->belongsTo(GarduInduk::class);
    }
}
