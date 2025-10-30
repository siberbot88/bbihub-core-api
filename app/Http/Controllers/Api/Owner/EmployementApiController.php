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
     * List semua karyawan milik owner (return JSON array + 200).
     */
    public function index(Request $request)
    {
        $owner = $request->user();

        $workshopIds = $owner->workshops()->pluck('id');

        $employees = Employment::whereIn('workshop_uuid', $workshopIds)
            ->with([
                'user',
                'user.roles:name',
                'workshop:id,name,user_uuid',
            ])
            ->get();
        return response()->json($employees, 200);
    }

    public function store(Request $request)
    {
        $owner = $request->user();

        $validator = Validator::make($request->all(), [
            // Users
            'name'                  => 'required|string|max:255',
            'username'              => 'required|string|max:255|unique:users',
            'email'                 => 'required|string|email|max:255|unique:users',
            'password'              => ['required', 'confirmed', Password::defaults()],
            'photo'                 => 'nullable|string|url',
            'role'                  => 'required|string|in:admin,mechanic',

            // Employment
            'workshop_uuid'         => [
                'required', 'uuid',
                Rule::exists('workshops', 'id')->where(fn ($q) => $q->where('user_uuid', $owner->id)),
            ],
            'specialist'            => 'nullable|string|max:255',
            'jobdesk'               => 'nullable|string',
            'status'                => 'nullable|in:active,inactive', // default active
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();

        try {
            $newUser = User::create([
                'id'       => Str::uuid(),
                'name'     => $request->name,
                'username' => $request->username,
                'email'    => $request->email,
                'password' => Hash::make($request->password),
                'photo'    => $request->photo ?: 'https://placehold.co/400x400/000000/FFFFFF?text=' . strtoupper(substr($request->name, 0, 2)),
            ]);

            $newUser->assignRole($request->role);

            $lastCode = Employment::max('code') ?? 'ST00000';
            $nextNum  = (int) substr($lastCode, 2) + 1;
            $newCode  = 'ST' . str_pad($nextNum, 5, '0', STR_PAD_LEFT);

            $employment = Employment::create([
                'id'            => Str::uuid(),
                'user_uuid'     => $newUser->id,
                'workshop_uuid' => $request->workshop_uuid,
                'code'          => $newCode,
                'specialist'    => $request->specialist,
                'jobdesk'       => $request->jobdesk,
                'status'        => $request->input('status', 'active'),
            ]);

            DB::commit();

            $employment->load('user', 'user.roles:name', 'workshop:id,name,user_uuid');

            return response()->json($employment, 201);

        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Gagal membuat karyawan.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function show(Request $request, Employment $employee)
    {
        if ($employee->workshop->user_uuid !== $request->user()->id) {
            return response()->json(['message' => 'Tidak diizinkan'], 403);
        }

        $employee->load('user', 'user.roles:name', 'workshop:id,name,user_uuid');

        return response()->json($employee, 200);
    }

    /**
     * Update data karyawan (200).
     */
    public function update(Request $request, Employment $employee)
    {
        $owner = $request->user();

        if ($employee->workshop->user_uuid !== $owner->id) {
            return response()->json(['message' => 'Tidak diizinkan'], 403);
        }

        $user = $employee->user;

        $validator = Validator::make($request->all(), [
            'name'      => 'sometimes|required|string|max:255',
            'username'  => ['sometimes','required','string','max:255', Rule::unique('users')->ignore($user->id)],
            'email'     => ['sometimes','required','string','email','max:255', Rule::unique('users')->ignore($user->id)],
            'password'  => ['nullable', 'confirmed', Password::defaults()],

            'role'      => 'sometimes|required|string|in:admin,mechanic',

            'specialist'=> 'nullable|string|max:255',
            'jobdesk'   => 'nullable|string',
            'status'    => 'nullable|in:active,inactive',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();

        try {
            $user->fill($request->only('name', 'username', 'email'));
            if ($request->filled('password')) {
                $user->password = Hash::make($request->password);
            }
            $user->save();

            if ($request->filled('role')) {
                $user->syncRoles([$request->role]);
            }

            $employee->fill($request->only('specialist', 'jobdesk', 'status'))->save();

            DB::commit();

            $employee->load('user', 'user.roles:name', 'workshop:id,name,user_uuid');

            return response()->json($employee, 200);

        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Gagal update karyawan.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }


    public function updateStatus(Request $request, Employment $employee)
    {
        if ($employee->workshop->user_uuid !== $request->user()->id) {
            return response()->json(['message' => 'Tidak diizinkan'], 403);
        }

        $request->validate([
            'status' => ['required', Rule::in(['active','inactive'])],
        ]);

        $employee->update(['status' => $request->status]);

        return response()->json([
            'message' => 'Status updated',
            'id'      => $employee->id,
            'status'  => $employee->status,
        ], 200);
    }

    /**
     * Hapus karyawan (204).
     */
    public function destroy(Request $request, Employment $employee)
    {
        if ($employee->workshop->user_uuid !== $request->user()->id) {
            return response()->json(['message' => 'Tidak diizinkan'], 403);
        }

        DB::beginTransaction();

        try {
            $user = $employee->user;

            $employee->delete();
            $user->delete();

            DB::commit();

            return response()->json(null, 204);

        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Gagal menghapus karyawan.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
