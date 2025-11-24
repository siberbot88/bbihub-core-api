<?php

namespace App\Livewire\Forms;

use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Validate;
use Livewire\Form;

class LoginForm extends Form
{
    private const ALLOWED_LOGIN_ROLES = ['superadmin'];
    private const MAX_LOGIN_ATTEMPTS  = 5;
    private const DECAY_SECONDS       = 60;

    #[Validate('required|string|email')]
    public string $email = '';

    #[Validate('required|string')]
    public string $password = '';

    #[Validate('boolean')]
    public bool $remember = false;

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        // Find user first
        $user = User::where('email', $this->email)->first();

        if (! Auth::attempt($this->only(['email', 'password']), $this->remember)) {
            RateLimiter::hit($this->throttleKey(), self::DECAY_SECONDS);

            throw ValidationException::withMessages([
                'form.email' => 'Email atau password salah.',
            ]);
        }

        // Check if user has superadmin role
        /** @var User $authenticatedUser */
        $authenticatedUser = Auth::user();
        
        if (! $authenticatedUser->hasAnyRole(self::ALLOWED_LOGIN_ROLES, 'web')) {
            Auth::logout();
            
            throw ValidationException::withMessages([
                'form.email' => 'Akun Anda tidak memiliki izin untuk mengakses aplikasi ini. Hanya superadmin yang dapat login.',
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Ensure the authentication request is not rate limited.
     */
    protected function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), self::MAX_LOGIN_ATTEMPTS)) {
            return;
        }

        event(new Lockout(request()));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'form.email' => 'Terlalu banyak percobaan login. Coba lagi dalam ' . $seconds . ' detik.',
        ]);
    }

    /**
     * Get the authentication rate limiting throttle key.
     */
    protected function throttleKey(): string
    {
        $email = Str::lower($this->email);
        return 'login:' . sha1($email . '|' . request()->ip());
    }
}
