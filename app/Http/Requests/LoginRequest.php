<?php

namespace App\Http\Requests;

use App\Models\MathCaptcha;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'email' => trim(strtolower($this->email ?? '')),
        ]);
    }

    public function rules(): array
    {
        return [
            'email'              => ['required', 'email', 'exists:users,email'],
            'password'           => ['required', 'string'],
            'captcha_token'      => ['required', 'string', 'exists:math_captchas,token'],
            'captcha_answer'     => ['required', 'integer'],
            'fingerprint_id'         => ['nullable', 'string', 'max:64'],
            'device_info'            => ['nullable', 'array'],
            'device_info.os'         => ['nullable', 'string'],
            'device_info.browser'    => ['nullable', 'string'],
            'device_info.device_type' => ['nullable', 'string'],
            'device_info.screen'     => ['nullable', 'string'],
            'device_info.language'   => ['nullable', 'string'],
            'device_info.timezone'   => ['nullable', 'string'],
            'device_info.timezone_offset' => ['nullable', 'integer'],
            'device_info.platform'   => ['nullable', 'string'],
            'device_info.hardware_concurrency' => ['nullable', 'integer'],
            'device_info.device_memory' => ['nullable', 'integer'],
            'device_info.max_touch_points' => ['nullable', 'integer'],
            'device_info.color_depth' => ['nullable', 'integer'],
            'device_info.pixel_ratio' => ['nullable', 'numeric'],
            'device_info.screen_orientation' => ['nullable', 'string'],
            'device_info.webgl_vendor' => ['nullable', 'string'],
            'device_info.webgl_renderer' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required'          => 'Email wajib diisi.',
            'email.email'             => 'Format email tidak valid.',
            'email.exists'            => 'Email tidak terdaftar.',
            'password.required'       => 'Password wajib diisi.',
            'captcha_token.required'  => 'Captcha wajib dimuat.',
            'captcha_token.exists'    => 'Captcha sudah kedaluwarsa. Silakan muat ulang.',
            'captcha_answer.required' => 'Jawaban captcha wajib diisi.',
            'captcha_answer.integer'  => 'Jawaban captcha harus berupa angka.',
        ];
    }

    public function validateCaptcha(): bool
    {
        $captcha = MathCaptcha::where('token', $this->captcha_token)->first();

        if (!$captcha || $captcha->isExpired()) {
            return false;
        }

        $valid = (int) $this->captcha_answer === $captcha->answer;

        $captcha->delete();

        return $valid;
    }
}
