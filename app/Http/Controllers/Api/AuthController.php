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
            ], 422); // 422 Unprocessable Entity
        }

        // Buat user baru
        try {
            $user = User::create([
                'id' => Str::uuid(), // Otomatis generate UUID
                'name' => $request->name,
                'username' => $request->username,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'photo' => 'https://placehold.co/400x400/000000/FFFFFF?text=' . substr($request->name, 0, 2),
            ]);

            // PENTING: Berikan role 'owner' menggunakan Spatie
            // Pastikan Anda sudah menjalankan RoleSeeder.php
            $user->assignRole('owner');

            // Buat token Sanctum untuk user
            $token = $user->createToken('auth_token_for_' . $user->username)->plainTextToken;

            return response()->json([
                'message' => 'Registrasi berhasil. Akun Owner telah dibuat.',
                'access_token' => $token,
                'token_type' => 'Bearer',
                'user' => $user->load('roles:name')
            ], 201); // 201 Created

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Registrasi gagal, terjadi kesalahan.',
                'error' => $e->getMessage()
            ], 500); // 500 Internal Server Error
        }
    }

    /**
     * Login untuk semua user (Owner, Admin, Mechanic).
     * * Memeriksa kredensial dan memberikan token Sanctum jika berhasil.
     */
    public function login(Request $request)
    {
        // Validasi input
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Cari user berdasarkan email
        $user = User::where('email', $request->email)->first();

        // Cek user dan password
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Email atau password salah.'
            ], 401); // 401 Unauthorized
        }

        // PENTING: Cek apakah user punya role yang diizinkan untuk login ke aplikasi ini
        if (!$user->hasAnyRole(['owner', 'admin', 'mechanic'])) {
            return response()->json([
                'message' => 'Akun Anda tidak memiliki izin untuk mengakses aplikasi ini.'
            ], 403); // 403 Forbidden
        }

        // Hapus token lama jika ada
        //$user->tokens()->delete();

        // Buat token Sanctum baru
        $token = $user->createToken('auth_token_for_' . $user->username)->plainTextToken;

        // Ambil role-nya dulu
        $role = $user->getRoleNames()->first();

        return response()->json([
            'message' => 'Login berhasil',
            'access_token' => $token,
            'token_type' => 'Bearer',
            // Kirim data user secara spesifik
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
                'email' => $user->email,
                'photo' => $user->photo,
                'role' => $role
            ]
        ], 200);
    }

    /**
     * Logout user (Memerlukan token).
     * * Menghapus token yang sedang digunakan.
     */
    public function logout(Request $request)
    {
        try {
            // Menghapus token yang sedang digunakan untuk otentikasi
            $request->user()->currentAccessToken()->delete();

            return response()->json([
                'message' => 'Logout berhasil'
            ], 200); // 200 OK

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Logout gagal, terjadi kesalahan.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
