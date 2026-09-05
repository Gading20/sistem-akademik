<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'password.required' => 'Password wajib diisi.',
        ];
    }

    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $credentials = $this->only('email', 'password');

        if (!Auth::attempt($credentials, $this->boolean('remember'))) {
            event(new Lockout($this));

            throw ValidationException::withMessages([
                'email' => ['Email atau password salah.'],
            ]);
        }

        // Login sukses → reset penghitung percobaan agar tidak terkunci
        // setelah beberapa kali gagal di masa lalu.
        RateLimiter::clear(str($this->input('email'))->lower()->value());
    }

    public function ensureIsNotRateLimited(): void
    {
        $throttleKey = str($this->input('email'))->lower()->value();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            event(new Lockout($this));

            throw ValidationException::withMessages([
                'email' => [trans('auth.throttle', [
                    'seconds' => $seconds,
                    'minutes' => ceil($seconds / 60),
                ])],
            ]);
        }

        RateLimiter::increment($throttleKey);
    }
}
