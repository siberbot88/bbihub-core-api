<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\RateLimiter;

class AuthController extends Controller
{
    /** Role yang diperbolehkan login ke aplikasi mobile (guard 'sanctum') */
    private const ALLOWED_LOGIN_ROLES = ['owner', 'admin']; // <-- Saya tambahkan 'mechanic'
    private const MAX_LOGIN_ATTEMPTS = 5;
    private const DECAY_SECONDS       = 60;

    /**
     * Helper: pastikan role tersedia di guard tertentu, kalau belum ada dibuat.
     */
    private function ensureRoleExistsForGuard(string $roleName, string $guard): Role
    {
        try {
            return Role::findByName($roleName, $guard);
        } catch (\Throwable $e) {
            return Role::create(['name' => $roleName, 'guard_name' => $guard]);
        }
    }

    /**
     * Helper: muat relasi sesuai role agar payload user ringkas & kontekstual.
     * Dijalankan SETELAH user terotentikasi (via 'sanctum').
     */
    private function loadUserRelations(User $user): void
    {
        if ($user->hasRole('owner', 'sanctum')) {
            $user->load('roles:name', 'workshops');
        } else {
            // Untuk admin dan mechanic
            $user->load('roles:name', 'employment.workshop');
        }
    }

    /**
     * Helper: key untuk rate limiter login.
     */
    private function loginThrottleKey(Request $request): string
    {
        $email = Str::lower((string) $request->input('email'));
        return 'login:' . sha1($email . '|' . $request->ip());
    }

    /**
     * GET /v1/auth/user
     * (Route ini harus dilindungi oleh middleware 'auth:sanctum')
     */
    public function me(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $this->loadUserRelations($user);

        return response()->json($user, 200);
    }

    /**
     * POST /v1/auth/register
     * Register owner (default).
     */
    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name'                  => ['required', 'string', 'max:255'],
            'username'              => ['required', 'string', 'max:255', 'unique:users,username'],
            'email'                 => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password'              => ['required', 'confirmed', Password::defaults()],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validasi gagal',
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            /** @var User $user */
            $user = User::create([
                'id'       => Str::uuid(),
                'name'     => $request->name,
                'username' => $request->username,
                'email'    => $request->email,
                'password' => Hash::make($request->password),
                'photo'    => 'https://placehold.co/400x400/000000/FFFFFF?text=' . strtoupper(substr($request->name, 0, 2)),
                'must_change_password' => false,
            ]);

            $role = $this->ensureRoleExistsForGuard('owner', 'sanctum');
            $user->guard_name = 'sanctum';
            $user->assignRole($role);

            $token = $user->createToken('auth_token_for_' . ($user->username ?? $user->email))->plainTextToken;

            $this->loadUserRelations($user);

            return response()->json([
                'message'      => 'Registrasi berhasil. Akun Owner telah dibuat.',
                'access_token' => $token,
                'token_type'   => 'Bearer',
                'user'         => [
                    'id'    => $user->id,
                    'name'  => $user->name,
                    'email' => $user->email,
                    'username' => $user->username,
                    'roles' => $user->roles,
                    'must_change_password' => (bool) $user->must_change_password,
                    'workshops' => $user->relationLoaded('workshops') ? $user->workshops : null,
                ],
            ], 201);

        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Registrasi gagal.',
                'error'   => config('app.debug') ? $e->getMessage() : 'Server error',
            ], 500);
        }
    }

    /**
     * POST /v1/auth/login
     * Login (owner/admin/mechanic).
     */
    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validasi gagal',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $key = $this->loginThrottleKey($request);
        if (RateLimiter::tooManyAttempts($key, self::MAX_LOGIN_ATTEMPTS)) {
            $seconds = RateLimiter::availableIn($key);
            return response()->json([
                'message' => 'Terlalu banyak percobaan login. Coba lagi dalam ' . $seconds . ' detik.',
            ], 429);
        }

        /** @var User|null $user */
        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            RateLimiter::hit($key, self::DECAY_SECONDS);
            return response()->json(['message' => 'Email atau password salah.'], 401);
        }

        RateLimiter::clear($key);

        if (! $user->hasAnyRole(self::ALLOWED_LOGIN_ROLES, 'sanctum')) {
            return response()->json(['message' => 'Akun Anda tidak memiliki izin untuk mengakses aplikasi ini.'], 403);
        }

        if ((bool) $request->boolean('revoke_others', false)) {
            $user->tokens()->delete();
        }

        $token = $user->createToken('auth_token_for_' . ($user->username ?? $user->email))->plainTextToken;

        $this->loadUserRelations($user);

        return response()->json([
            'message'      => 'Login berhasil',
            'access_token' => $token,
            'token_type'   => 'Bearer',
            'user'         => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
                'username' => $user->username,
                'roles' => $user->roles,
                'must_change_password' => (bool) $user->must_change_password,
                'workshops' => $user->relationLoaded('workshops') ? $user->workshops : null,
                'employment' => $user->relationLoaded('employment') ? $user->employment : null,
            ],
        ], 200);
    }

    /**
     * POST /v1/auth/change-password
     * (Route ini harus dilindungi oleh middleware 'auth:sanctum')
     */
    public function changePassword(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        // Cek apakah user sedang dalam status 'harus ganti password'
        $mustChange = (bool) $user->must_change_password;

        $rules = [
            'new_password' => ['required', 'string', 'min:8', 'confirmed'], // 'confirmed' butuh 'new_password_confirmation'
        ];

        // Jika INI BUKAN ganti password pertama kali (mustChange = false),
        // maka WAJIB menyertakan password lama (current_password)
        if (! $mustChange) {
            $rules['current_password'] = ['required', 'string', 'min:6'];
        }

        $validator = Validator::make($request->all(), $rules, [
            'new_password.confirmed' => 'Konfirmasi password baru tidak cocok.',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validasi gagal',
                'errors'  => $validator->errors(),
            ], 422);
        }

        // Cek password lama (hanya jika diperlukan)
        if (! $mustChange) {
            if (! Hash::check($request->input('current_password'), $user->password)) {
                return response()->json([
                    'message' => 'Validasi gagal',
                    'errors' => ['current_password' => ['Password saat ini salah']]
                ], 422);
            }
        }

        // Update password dan reset flag
        $user->forceFill([
            'password' => Hash::make($request->input('new_password')),
            'must_change_password' => false, // Setel kembali ke false
            'password_changed_at' => now(),
        ])->save();

        return response()->json(['message' => 'Password berhasil diperbarui'], 200);
    }

    /**
     * POST /v1/auth/logout
     * (Route ini harus dilindungi oleh middleware 'auth:sanctum')
     */
    public function logout(Request $request): JsonResponse
    {
        try {
            /** @var User $user */
            $user = $request->user();

            if ($request->boolean('all', false)) {
                $user->tokens()->delete();
            } else {
                $user->currentAccessToken()->delete();
            }

            return response()->json(['message' => 'Logout berhasil'], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Logout gagal.',
                'error'   => config('app.debug') ? $e->getMessage() : 'Server error',
            ], 500);
        }
    }
}
