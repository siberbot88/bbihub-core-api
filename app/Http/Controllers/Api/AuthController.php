<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Auth\ChangePasswordRequest;
use App\Http\Requests\Api\Auth\LoginRequest;
use App\Http\Requests\Api\Auth\RegisterRequest;
use App\Http\Traits\ApiResponseTrait;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class AuthController extends Controller
{
    use ApiResponseTrait;



    /**
     * Helper: muat relasi sesuai role agar payload user ringkas & kontekstual.
     * Dijalankan SETELAH user terotentikasi (via 'sanctum').
     */
    private function loadUserRelations(User $user): void
    {
        if ($user->hasRole('owner', 'sanctum')) {
            $user->load('roles:name', 'workshops', 'ownerSubscription.plan');
        } else {
            $user->load('roles:name', 'employment.workshop');
        }
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

        // Add trial information to response
        $userData = $user->toArray();
        $userData['subscription_status'] = $user->getSubscriptionStatus();
        $userData['is_in_trial'] = $user->isInTrial();
        $userData['trial_ends_at'] = $user->trial_ends_at?->toIso8601String();
        $userData['trial_days_remaining'] = $user->trialDaysRemaining();
        $userData['has_premium_access'] = $user->hasPremiumAccess();

        return $this->successResponse('User data retrieved', $userData);
    }

    /**
     * POST /v1/auth/register
     * Register owner (default).
     */
    public function register(RegisterRequest $request): JsonResponse
    {

        try {
            /** @var User $user */
            $user = User::create([
                'id' => Str::uuid(),
                'name' => $request->input('name'),
                'username' => $request->input('username'),
                'email' => $request->input('email'),
                'password' => Hash::make($request->input('password')),
                'photo' => 'https://placehold.co/400x400/000000/FFFFFF?text=' . strtoupper(substr($request->input('name'), 0, 2)),
                'must_change_password' => false,
            ]);

            // Role 'owner' must exist in database (seeded)
            $user->assignRole('owner');
            // Force guard name if needed, though assignRole usually handles it based on config
            // But to be safe with previous logic:
            // $user->guard_name = 'sanctum'; // Usually not needed if model has guard_name property or default

            $token = $user->createToken('auth_token_for_' . ($user->username ?? $user->email))->plainTextToken;

            $this->loadUserRelations($user);

            return $this->successResponse('Registrasi berhasil. Akun Owner telah dibuat.', [
                'access_token' => $token,
                'token_type' => 'Bearer',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'username' => $user->username,
                    'roles' => $user->roles,
                    'must_change_password' => (bool) $user->must_change_password,
                    'workshops' => $user->relationLoaded('workshops') ? $user->workshops : null,
                    'owner_subscription' => $user->relationLoaded('ownerSubscription') ? $user->ownerSubscription : null,
                ],
            ], 201);
        } catch (\Throwable $e) {
            return $this->errorResponse(
                'Registrasi gagal.',
                500,
                config('app.debug') ? $e->getMessage() : 'Server error'
            );
        }
    }

    /**
     * POST /v1/auth/login
     * Login (owner/admin/mechanic).
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $request->authenticate();

        /** @var User $user */
        $user = $request->user(); // Ambil user yang sudah diotentikasi oleh LoginRequest

        if ((bool) $request->boolean('revoke_others', false)) {
            $user->tokens()->delete();
        }

        // Support remember-me with extended token expiration
        $tokenName = 'auth_token_for_' . ($user->username ?? $user->email);
        $remember = $request->boolean('remember', false);

        if ($remember) {
            // Extended expiration: 30 days
            $token = $user->createToken($tokenName, ['*'], now()->addDays(30))->plainTextToken;
        } else {
            // Default expiration based on sanctum config
            $token = $user->createToken($tokenName)->plainTextToken;
        }

        $this->loadUserRelations($user);

        // Audit log: User logged in
        AuditLog::log(
            event: 'login',
            user: $user,
            newValues: [
                'remember' => $remember,
                'token_expires' => $remember ? '30 days' : 'session',
            ]
        );

        return $this->successResponse('Login berhasil', [
            'access_token' => $token,
            'token_type' => 'Bearer',
            'remember' => $remember,
            'expires_in' => $remember ? '30 days' : 'session',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'username' => $user->username,
                'roles' => $user->roles,
                'must_change_password' => (bool) $user->must_change_password,
                'workshops' => $user->relationLoaded('workshops') ? $user->workshops : null,
                'employment' => $user->relationLoaded('employment') ? $user->employment : null,
                'owner_subscription' => $user->relationLoaded('ownerSubscription') ? $user->ownerSubscription : null,
            ],
        ]);
    }

    /**
     * POST /v1/auth/change-password
     * (Route ini harus dilindungi oleh middleware 'auth:sanctum')
     */
    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        // Validasi (termasuk cek 'current_password' jika perlu)
        // sudah ditangani oleh ChangePasswordRequest.

        /** @var User $user */
        $user = $request->user();

        $user->forceFill([
            'password' => Hash::make($request->input('new_password')),
            'must_change_password' => false,
            'password_changed_at' => now(),
        ])->save();

        // Audit log: Password changed
        AuditLog::log(
            event: 'password_changed',
            user: $user,
            auditable: $user
        );

        return $this->successResponse('Password berhasil diperbarui');
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

            // Audit log: User logged out
            AuditLog::log(
                event: 'user_logged_out',
                user: $user,
                newValues: ['all_tokens' => $request->boolean('all', false)]
            );

            return $this->successResponse('Logout berhasil');
        } catch (\Throwable $e) {
            return $this->errorResponse(
                'Logout gagal.',
                500,
                config('app.debug') ? $e->getMessage() : 'Server error'
            );
        }
    }
}
