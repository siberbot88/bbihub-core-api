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

class AuthController extends Controller
{
    /**
     * Helper: assign role ke beberapa guard sekaligus (web & sanctum).
     */
    private function assignRoleForGuards(User $user, string $roleName, array $guards = ['web', 'sanctum']): void
    {
        foreach ($guards as $guard) {
            // Jika role belum dibuat untuk guard tsb, abaikan
            try {
                $role = Role::findByName($roleName, $guard);
                $user->assignRole($role);
            } catch (\Throwable $e) {
                // silent ignore; pastikan RoleSeeder membuat role untuk web & sanctum
            }
        }
    }

    /**
     * Register owner (default).
     */
    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name'                  => 'required|string|max:255',
            'username'              => 'required|string|max:255|unique:users,username',
            'email'                 => 'required|string|email|max:255|unique:users,email',
            'password'              => ['required', 'confirmed', Password::defaults()],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validasi gagal',
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            $user = User::create([
                'id'       => Str::uuid(),
                'name'     => $request->name,
                'username' => $request->username,
                'email'    => $request->email,
                'password' => Hash::make($request->password),
                'photo'    => 'https://placehold.co/400x400/000000/FFFFFF?text=' . strtoupper(substr($request->name, 0, 2)),
            ]);

            // Beri role OWNER untuk kedua guard
            $this->assignRoleForGuards($user, 'owner');

            // Buat token Sanctum
            $token = $user->createToken('auth_token_for_'.$user->username)->plainTextToken;

            // Muat relasi sesuai role
            $user->load('roles:name', 'workshops');

            return response()->json([
                'message'      => 'Registrasi berhasil. Akun Owner telah dibuat.',
                'access_token' => $token,
                'token_type'   => 'Bearer',
                'user'         => $user,
            ], 201);

        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Registrasi gagal.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Login (owner/admin/mechanic).
     */
    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Email atau password salah.'], 401);
        }

        // Pastikan user punya salah satu role aplikasi
        if (! $user->hasAnyRole(['owner', 'admin', 'mechanic'])) {
            return response()->json(['message' => 'Akun Anda tidak memiliki izin untuk mengakses aplikasi ini.'], 403);
        }

        // Token Sanctum
        $token = $user->createToken('auth_token_for_'.$user->username)->plainTextToken;

        // Relasi tergantung role
        if ($user->hasRole('owner')) {
            $user->load('roles:name', 'workshops');
        } else {
            $user->load('roles:name', 'employment.workshop');
        }

        return response()->json([
            'message'      => 'Login berhasil',
            'access_token' => $token,
            'token_type'   => 'Bearer',
            'user'         => $user,
        ], 200);
    }

    /**
     * Logout (revoke current token).
     */
    public function logout(Request $request): JsonResponse
    {
        try {
            $request->user()?->currentAccessToken()?->delete();

            return response()->json(['message' => 'Logout berhasil'], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Logout gagal.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
