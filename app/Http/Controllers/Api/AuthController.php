<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        // Validasi input
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = User::create([
                'id' => Str::uuid(),
                'name' => $request->name,
                'username' => $request->username,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'photo' => 'https://placehold.co/400x400/000000/FFFFFF?text=' . substr($request->name, 0, 2),
            ]);

            // Beri role owner
            $user->assignRole('owner');

            // Buat token
            $token = $user->createToken('auth_token_for_' . $user->username)->plainTextToken;

            // Load relasi sesuai role
            $user->load('roles:name', 'workshops');

            return response()->json([
                'message' => 'Registrasi berhasil. Akun Owner telah dibuat.',
                'access_token' => $token,
                'token_type' => 'Bearer',
                'user' => $user
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Registrasi gagal.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Email atau password salah.'
            ], 401);
        }

        if (!$user->hasAnyRole(['owner', 'admin', 'mechanic'])) {
            return response()->json([
                'message' => 'Akun Anda tidak memiliki izin untuk mengakses aplikasi ini.'
            ], 403);
        }

        // Buat token baru
        $token = $user->createToken('auth_token_for_' . $user->username)->plainTextToken;

        // Load relasi berdasarkan role
        if ($user->hasRole('owner')) {
            $user->load('roles:name', 'workshops');
        } else {
            $user->load('roles:name', 'employment.workshop');
        }

        return response()->json([
            'message' => 'Login berhasil',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user
        ], 200);
    }

    public function logout(Request $request)
    {
        try {
            $request->user()->currentAccessToken()->delete();

            return response()->json([
                'message' => 'Logout berhasil'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Logout gagal.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
