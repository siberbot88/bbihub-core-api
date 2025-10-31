<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;
use Illuminate\Http\JsonResponse;

class AuthController extends Controller
{
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

            $user->assignRole(Role::findByName('owner', 'web'));

            $token = $user->createToken('auth_token_for_' . $user->username)->plainTextToken;

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
            return response()->json([
                'message' => 'Email atau password salah.',
            ], 401);
        }

        if (! $user->hasAnyRole(['owner', 'admin', 'mechanic'])) {
            return response()->json([
                'message' => 'Akun Anda tidak memiliki izin untuk mengakses aplikasi ini.',
            ], 403);
        }

        $token = $user->createToken('auth_token_for_' . $user->username)->plainTextToken;

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
            $token = $request->user()?->currentAccessToken();
            if ($token) {
                $token->delete();
            }

            return response()->json([
                'message' => 'Logout berhasil',
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Logout gagal.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
