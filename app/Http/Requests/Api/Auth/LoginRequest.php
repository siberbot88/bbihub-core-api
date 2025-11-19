<?php

namespace App\Http\Requests\Api\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    private const ALLOWED_LOGIN_ROLES = ['owner', 'admin'];
    private const MAX_LOGIN_ATTEMPTS  = 5;
    private const DECAY_SECONDS       = 60;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Coba untuk mengotentikasi kredensial request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        /** @var User|null $user */
        $user = User::where('email', $this->input('email'))->first();

        if (! $user || ! Hash::check($this->input('password'), $user->password)) {
            RateLimiter::hit($this->throttleKey(), self::DECAY_SECONDS);

            throw ValidationException::withMessages([
                'email' => 'Email atau password salah.',
            ]);
        }

        if (! $user->hasAnyRole(self::ALLOWED_LOGIN_ROLES, 'sanctum')) {
            throw ValidationException::withMessages([
                'email' => 'Akun Anda tidak memiliki izin untuk mengakses aplikasi ini.',
            ]);
        }

        $this->setUserResolver(function () use ($user) {
            return $user;
        });

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Pastikan request login tidak di-rate limited.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), self::MAX_LOGIN_ATTEMPTS)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => 'Terlalu banyak percobaan login. Coba lagi dalam ' . $seconds . ' detik.',
        ]);
    }

    /**
     * Dapatkan throttle key untuk request ini.
     */
    public function throttleKey(): string
    {
        $email = Str::lower((string) $this->input('email'));
        return 'login:' . sha1($email . '|' . $this->ip());
    }
}
