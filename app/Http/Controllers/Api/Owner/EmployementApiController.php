<?php

namespace App\Http\Controllers\Api\Owner;

use App\Http\Controllers\Controller;
use App\Mail\StaffCredentialsMail;
use App\Models\Employment;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class EmployementApiController extends Controller
{
    /** Role yang otomatis dikirim email kredensial */
    private const ROLES_NEED_EMAIL = ['admin', 'mechanic', 'teknisi', 'technician'];

    /* =================== Helpers =================== */

    /**
     * Pastikan role dengan guard tertentu ada (kalau belum, buat baru).
     */
    private function ensureRoleExistsForGuard(string $roleName, string $guard): Role
    {
        try {
            return Role::findByName($roleName, $guard);
        } catch (\Throwable $e) {
            return Role::create([
                'name'       => $roleName,
                'guard_name' => $guard,
            ]);
        }
    }

    /** Password acak 8 char (huruf besar/kecil + angka, tanpa karakter mirip) */
    private function generatePassword(int $length = 8): string
    {
        $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghjkmnpqrstuvwxyz23456789';
        $out = '';
        $max = strlen($alphabet) - 1;

        for ($i = 0; $i < $length; $i++) {
            $out .= $alphabet[random_int(0, $max)];
        }

        return $out;
    }

    /** URL aplikasi / halaman login staff (ambil dari config atau .env) */
    private function employeeAppUrl(): string
    {
        return (string) (config('services.employee_app.url')
            ?: env('EMPLOYEE_APP_URL', 'bengkelapp://login'));
    }

    /* =================== Endpoints =================== */

    /**
     * GET /v1/owners/employee
     */
    public function index(Request $request): JsonResponse
    {
        $owner = $request->user();

        $workshopIds = $owner->workshops()->pluck('id');

        $employees = Employment::whereIn('workshop_uuid', $workshopIds)
            ->with([
                'user',
                'user.roles:name',
                'workshop:id,name,user_uuid',
            ])->get();

        return response()->json($employees, 200);
    }

    /**
     * POST /v1/owners/employee
     * - Owner mendaftarkan staff (admin / mechanic / teknisi)
     * - Password auto-generate (8 char) dan disimpan HASH-nya
     * - must_change_password = true (dipaksa ganti saat login pertama)
     * - Email kredensial dikirim hanya ke role tertentu
     */
    public function store(Request $request): JsonResponse
    {
        $owner = $request->user();

        $validator = Validator::make($request->all(), [
            // Users (tanpa input password)
            'name'          => ['required', 'string', 'max:255'],
            'username'      => ['required', 'string', 'max:255', 'unique:users,username'],
            'email'         => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'photo'         => ['nullable', 'string', 'url'],
            'role'          => ['required', 'string', Rule::in(self::ROLES_NEED_EMAIL)],

            // Employment
            'workshop_uuid' => [
                'required',
                'uuid',
                Rule::exists('workshops', 'id')
                    ->where(fn($q) => $q->where('user_uuid', $owner->id)),
            ],
            'specialist'    => ['nullable', 'string', 'max:255'],
            'jobdesk'       => ['nullable', 'string'],
            'status'        => ['nullable', Rule::in(['active', 'inactive'])],
        ], [
            'role.in' => 'Role harus salah satu dari: admin, mechanic/teknisi/technician.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validasi gagal',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();

        try {
            /**
             * Bungkus pembuatan user + employment dalam 1 transaksi DB.
             * Email dikirim SETELAH commit (di luar closure).
             */
            [$newUser, $employment, $plainPassword] = DB::transaction(
                function () use ($data) {
                    $plainPassword = $this->generatePassword(8);

                    /** @var User $user */
                    $user = User::create([
                        'id'       => Str::uuid(),
                        'name'     => trim($data['name']),
                        'username' => trim($data['username']),
                        'email'    => trim($data['email']),
                        'password' => Hash::make($plainPassword),
                        'photo'    => $data['photo']
                            ?? ('https://placehold.co/400x400/000000/FFFFFF?text='
                                . strtoupper(substr($data['name'], 0, 2))),
                        'must_change_password' => true,
                    ]);

                    $role = $this->ensureRoleExistsForGuard($data['role'], 'sanctum');
                    $user->guard_name = 'sanctum';
                    $user->assignRole($role);

                    $last = Employment::orderBy('code', 'desc')->lockForUpdate()->first();
                    $nextNum = 1;
                    if ($last && preg_match('/^ST(\d{5})$/', $last->code, $m)) {
                        $nextNum = (int) $m[1] + 1;
                    }
                    $newCode = 'ST' . str_pad((string) $nextNum, 5, '0', STR_PAD_LEFT);

                    $employment = Employment::create([
                        'id'            => Str::uuid(),
                        'user_uuid'     => $user->id,
                        'workshop_uuid' => $data['workshop_uuid'],
                        'code'          => $newCode,
                        'specialist'    => $data['specialist'] ?? null,
                        'jobdesk'       => $data['jobdesk'] ?? null,
                        'status'        => $data['status'] ?? 'active',
                    ]);

                    return [$user, $employment, $plainPassword];
                }
            );

            $emailSent = false;
            if (in_array($data['role'], self::ROLES_NEED_EMAIL, true)) {
                try {
                    Mail::to($newUser->email)->send(
                        new StaffCredentialsMail(
                            recipientName: $newUser->name,
                            username: $newUser->username,
                            plainPassword: $plainPassword,
                            loginUrl: $this->employeeAppUrl(),
                        )
                    );
                    $emailSent = true;
                } catch (\Throwable $mailErr) {
                    \Log::warning('Send staff credential mail failed', [
                        'user_id' => $newUser->id,
                        'error'   => $mailErr->getMessage(),
                    ]);
                }
            }

            $employment->load('user', 'user.roles:name', 'workshop:id,name,user_uuid');

            return response()->json([
                'message'    => 'Karyawan berhasil dibuat',
                'data'       => $employment,
                'email_sent' => $emailSent,
            ], 201);
        } catch (\Throwable $e) {
            \Log::error('Create employee failed', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Gagal membuat karyawan.',
                'error'   => config('app.debug') ? $e->getMessage() : 'Server error',
            ], 500);
        }
    }

    /**
     * GET /v1/owners/employee/{employee}
     */
    public function show(Request $request, Employment $employee): JsonResponse
    {
        if ($employee->workshop->user_uuid !== $request->user()->id) {
            return response()->json(['message' => 'Tidak diizinkan'], 403);
        }

        $employee->load('user', 'user.roles:name', 'workshop:id,name,user_uuid');

        return response()->json($employee, 200);
    }

    /**
     * PUT /v1/owners/employee/{employee}
     * (Tidak auto-kirim email; opsional ganti password manual)
     */
    public function update(Request $request, Employment $employee): JsonResponse
    {
        $owner = $request->user();

        if ($employee->workshop->user_uuid !== $owner->id) {
            return response()->json(['message' => 'Tidak diizinkan'], 403);
        }

        $user = $employee->user;

        $validator = Validator::make($request->all(), [
            'name'      => ['sometimes', 'required', 'string', 'max:255'],
            'username'  => ['sometimes', 'required', 'string', 'max:255',
                Rule::unique('users', 'username')->ignore($user->id),
            ],
            'email'     => ['sometimes', 'required', 'string', 'email', 'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'password'  => ['nullable', 'string', 'min:8', 'confirmed'],

            'role'      => ['sometimes', 'required', 'string', Rule::in(self::ROLES_NEED_EMAIL)],

            'specialist'=> ['nullable', 'string', 'max:255'],
            'jobdesk'   => ['nullable', 'string'],
            'status'    => ['nullable', Rule::in(['active', 'inactive'])],
        ], [
            'role.in' => 'Role harus salah satu dari: admin, mechanic/teknisi/technician.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validasi gagal',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();

        try {
            DB::transaction(function () use ($data, $user, $employee) {
                $user->fill(array_filter(
                    $data,
                    fn($key) => in_array($key, ['name', 'username', 'email'], true),
                    ARRAY_FILTER_USE_KEY
                ));

                if (!empty($data['password'])) {
                    $user->password = Hash::make($data['password']);
                    $user->must_change_password = false;
                    $user->password_changed_at = now();
                }

                $user->save();

                if (!empty($data['role'])) {
                    $role = $this->ensureRoleExistsForGuard($data['role'], 'sanctum');
                    $user->guard_name = 'sanctum';
                    $user->syncRoles([$role]);
                }

                $employee->fill(array_filter(
                    $data,
                    fn($key) => in_array($key, ['specialist', 'jobdesk', 'status'], true),
                    ARRAY_FILTER_USE_KEY
                ));

                $employee->save();
            });

            $employee->load('user', 'user.roles:name', 'workshop:id,name,user_uuid');

            return response()->json($employee, 200);
        } catch (\Throwable $e) {
            \Log::error('Update employee failed', ['error' => $e->getMessage()]);

            return response()->json([
                'message' => 'Gagal update karyawan.',
                'error'   => config('app.debug') ? $e->getMessage() : 'Server error',
            ], 500);
        }
    }

    /**
     * PATCH /v1/owners/employee/{employee}/status
     */
    public function updateStatus(Request $request, Employment $employee): JsonResponse
    {
        if ($employee->workshop->user_uuid !== $request->user()->id) {
            return response()->json(['message' => 'Tidak diizinkan'], 403);
        }

        $request->validate([
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ]);

        $employee->update(['status' => $request->status]);

        return response()->json([
            'message' => 'Status updated',
            'id'      => $employee->id,
            'status'  => $employee->status,
        ], 200);
    }

    /**
     * DELETE /v1/owners/employee/{employee}
     */
    public function destroy(Request $request, Employment $employee): JsonResponse
    {
        if ($employee->workshop->user_uuid !== $request->user()->id) {
            return response()->json(['message' => 'Tidak diizinkan'], 403);
        }

        try {
            DB::transaction(function () use ($employee) {
                $user = $employee->user;
                $user->tokens()->delete(); // Sanctum tokens

                $employee->delete();
                $user->delete();
            });

            return response()->json(null, 204);
        } catch (\Throwable $e) {
            \Log::error('Delete employee failed', ['error' => $e->getMessage()]);

            return response()->json([
                'message' => 'Gagal menghapus karyawan.',
                'error'   => config('app.debug') ? $e->getMessage() : 'Server error',
            ], 500);
        }
    }
}
