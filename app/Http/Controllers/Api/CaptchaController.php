<?php

namespace App\Http\Controllers\Api;

use App\Models\MathCaptcha;
use Illuminate\Support\Str;

class CaptchaController extends BaseApiController
{
    public function generate()
    {
        MathCaptcha::where('expires_at', '<', now())->delete();

        $a = random_int(1, 20);
        $b = random_int(1, 20);
        $operators = ['+', '-'];
        $operator = $operators[array_rand($operators)];

        if ($operator === '-' && $b > $a) {
            [$a, $b] = [$b, $a];
        }

        $answer = $operator === '+' ? $a + $b : $a - $b;
        $question = "$a $operator $b = ?";

        $token = Str::random(64);

        MathCaptcha::create([
            'token'      => $token,
            'question'   => $question,
            'answer'     => $answer,
            'expires_at' => now()->addMinutes(5),
        ]);

        return $this->success([
            'token'    => $token,
            'question' => $question,
        ], 'Captcha generated');
    }
}
