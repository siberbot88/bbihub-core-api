<?php

namespace App\Http\Controllers\Api\Owner;

use App\Http\Controllers\Controller;
use App\Models\Employment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class EmployementApiController extends Controller
{
    /**
     * Menampilkan semua karyawan milik Owner yang sedang login.
     */
    public function index(Request $request)
    {
        $owner = $request->user();

        // 1. Ambil semua ID workshop milik owner ini
        $workshopIds = $owner->workshops()->pluck('id');

        // 2. Ambil semua data employment dari workshop tsb.
        $employees = Employment::whereIn('workshop_uuid', $workshopIds)
            ->with('user', 'user.roles:name', 'workshop:id,name')
            ->get();

        if (!$employees){
            return response()->json(['message' => 'Data tidak ditemukan.'], 404);
        }
        return response()->json(['message'=> 'Success', 'data'=> $employees],200);
    }

    /**
     * Menyimpan karyawan baru (didaftarkan oleh Owner).
     * Ini adalah logic untuk form Flutter Anda.
     */
    public function store(Request $request)
    {
        $owner = $request->user();

        $validator = Validator::make($request->all(), [
            // Data untuk Tabel User
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'confirmed', Password::defaults()],
            'photo' => 'nullable|string|url',
            'role' => 'required|string|in:admin,mechanic',

            // Data untuk Tabel Employment
            'workshop_uuid' => [
                'required',
                'uuid',
                Rule::exists('workshops', 'id')->where(function ($query) use ($owner) {
                    $query->where('user_uuid', $owner->id);
                }),
            ],
            'specialist' => 'nullable|string|max:255',
            'jobdesk' => 'nullable|string',
            'status' => 'nullable|in:active,inactive',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();
        try {
            // --- Step 1: Buat Akun User Baru ---
            $newUser = User::create([
                'id' => Str::uuid(),
                'name' => $request->name,
                'username' => $request->username,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'photo' => $request->photo ?? 'https://placehold.co/400x400/000000/FFFFFF?text=' . substr($request->name, 0, 2),
            ]);

            // --- Step 2: Beri Role (Spatie) ---
            $newUser->assignRole($request->role);

            // --- Step 3: Buat Catatan Kepegawaian (Employment) ---
            $lastCode = Employment::max('code') ?? 'ST00000';
            $nextNum = (int)substr($lastCode, 2) + 1;
            $newCode = 'ST' . str_pad($nextNum, 5, '0', STR_PAD_LEFT);

            $employment = Employment::create([
                'id' => Str::uuid(),
                'user_uuid' => $newUser->id,
                'workshop_uuid' => $request->workshop_uuid,
                'code' => $newCode,
                'specialist' => $request->specialist,
                'jobdesk' => $request->jobdesk,
                'status' => $request->status,
            ]);

            DB::commit();


            $employment->load('user', 'user.roles:name');
            $employment->status = $data['status'] ?? 'active';
            $employment->save();
            return response()->json($employment, 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal membuat karyawan.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Menampilkan detail 1 karyawan.
     */
    public function show(Request $request, Employment $employee)
    {

        // Cek Keamanan: Apakah owner ini memiliki workshop tempat karyawan ini bekerja?
        if ($employee->workshop->user_uuid !== $request->user()->id) {
            return response()->json(['message' => 'Tidak diizinkan'], 403);
        }

        $employee->load('user', 'user.roles:name', 'workshop:id,name');
        return response()->json($employee);
    }

    /**
     * Update data karyawan.
     */
    public function update(Request $request, Employment $employee)
    {
        $owner = $request->user();

        // Cek Keamanan
        if ($employee->workshop->user_uuid !== $owner->id) {
            return response()->json(['message' => 'Tidak diizinkan'], 403);
        }

        $user = $employee->user;

        $validator = Validator::make($request->all(), [
            // Data User
            'name' => 'sometimes|required|string|max:255',
            'username' => ['sometimes','required','string','max:255', Rule::unique('users')->ignore($user->id)],
            'email' => ['sometimes','required','string','email','max:255', Rule::unique('users')->ignore($user->id)],
            'password' => ['nullable', 'confirmed', Password::defaults()],

            // Data Role
            'role' => 'sometimes|required|string|in:admin,mechanic',

            // Data Employment
            'specialist' => 'nullable|string|max:255',
            'jobdesk' => 'nullable|string',
            'status' => 'nullable|in:active,inactive',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();
        try {
            // --- Update User ---
            $user->update($request->only('name', 'username', 'email'));
            if ($request->filled('password')) {
                $user->password = Hash::make($request->password);
                $user->save();
            }

            // --- Update Role ---
            if ($request->filled('role')) {
                $user->syncRoles([$request->role]);
            }

            // --- Update Employment ---
            $employee->update($request->only('specialist', 'jobdesk'));

            DB::commit();

            $employee->load('user', 'user.roles:name', 'workshop:id,name');
            return response()->json($employee);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal update karyawan.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Menghapus karyawan.
     */
    public function destroy(Request $request, Employment $employee)
    {
        // Cek Keamanan
        if ($employee->workshop->user_uuid !== $request->user()->id) {
            return response()->json(['message' => 'Tidak diizinkan'], 403);
        }

        DB::beginTransaction();
        try {
            $user = $employee->user;

            // 1. Hapus data employment
            $employee->delete();

            // 2. Hapus data user (ini akan menghapus role Spatie juga)
            $user->delete();

            DB::commit();

            return response()->json(null, 204); // 204 No Content

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal menghapus karyawan.', 'error' => $e->getMessage()], 500);
        }
    }
}
