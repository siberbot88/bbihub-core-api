<?php

namespace App\Services;

use App\Mail\StaffCredentialsMail;
use App\Models\Employment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class EmploymentService
{
    /** Role yang otomatis dikirim email kredensial */
    private const ROLES_NEED_EMAIL = ['admin'];

    /**
     * Mengambil daftar karyawan dengan pagination dan pencarian.
     *
     * @param \Illuminate\Support\Collection|array $workshopIds
     * @param string|null $search
     * @param int $perPage
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getEmployees($workshopIds, ?string $search = null, int $perPage = 15)
    {
        $query = Employment::whereIn('workshop_uuid', $workshopIds)
            ->with([
                'user',
                'user.roles:name',
                'workshop:id,name,user_uuid',
            ]);

        if ($search) {
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%");
            });
        }

        return $query->latest()->paginate($perPage);
    }

    /**
     * Membuat Karyawan baru, User, dan mengirim email.
     *
     * @param array $data Data yang sudah divalidasi dari StoreEmploymentRequest
     * @return array [Employment $employment, bool $emailSent]
     * @throws \Throwable
     */
    public function createEmployee(array $data): array
    {
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

                // Logika generate code ST...
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

        $emailSent = $this->sendCredentialsEmail($newUser, $plainPassword, $data['role']);

        $employment->load('user', 'user.roles:name', 'workshop:id,name,user_uuid');
        return [$employment, $emailSent];
    }

    /**
     * Memperbarui Karyawan dan data User terkait.
     *
     * @param Employment $employment Model Karyawan
     * @param array $data Data yang sudah divalidasi dari UpdateEmploymentRequest
     * @return Employment
     * @throws \Throwable
     */
    public function updateEmployee(Employment $employment, array $data): Employment
    {
        $user = $employment->user;

        DB::transaction(function () use ($data, $user, $employment) {
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

            $employment->fill(array_filter(
                $data,
                fn($key) => in_array($key, ['specialist', 'jobdesk', 'status'], true),
                ARRAY_FILTER_USE_KEY
            ));

            $employment->save();
        });

        $employment->load('user', 'user.roles:name', 'workshop:id,name,user_uuid');
        return $employment;
    }

    /**
     * Memperbarui status Karyawan.
     */
    public function updateEmployeeStatus(Employment $employment, string $status): Employment
    {
        $employment->update(['status' => $status]);
        return $employment;
    }

    /**
     * Menghapus Karyawan dan User terkait.
     *
     * @param Employment $employment
     * @return void
     * @throws \Throwable
     */
    public function deleteEmployee(Employment $employment): void
    {
        DB::transaction(function () use ($employment) {
            $user = $employment->user;
            $user->tokens()->delete();

            $employment->delete();
            $user->delete();
        });
    }


    /* =================== Private Helpers =================== */

    /**
     * Kirim email kredensial jika role-nya sesuai.
     */
    private function sendCredentialsEmail(User $user, string $plainPassword, string $role): bool
    {
        if (!in_array($role, self::ROLES_NEED_EMAIL, true)) {
            return false;
        }

        try {
            Mail::to($user->email)->send(
                new StaffCredentialsMail(
                    recipientName: $user->name,
                    username: $user->username,
                    plainPassword: $plainPassword,
                    loginUrl: $this->employeeAppUrl(),
                )
            );
            return true;
        } catch (\Throwable $mailErr) {
            Log::warning('Send staff credential mail failed', [
                'user_id' => $user->id,
                'error'   => $mailErr->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Pastikan role dengan guard tertentu ada.
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

    /** Password acak 8 char */
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

    private function employeeAppUrl(): string
    {
        return (string) config('services.employee_app.url', 'bengkelapp://login');
    }
}
