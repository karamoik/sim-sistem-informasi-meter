<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MathCaptcha extends Model
{
    protected $fillable = ['token', 'question', 'answer', 'expires_at'];

    protected $hidden = ['answer'];

    public function isExpired(): bool
    {
        return now()->greaterThan($this->expires_at);
    }
}
