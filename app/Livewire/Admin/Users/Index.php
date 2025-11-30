<?php

namespace App\Livewire\Admin\Users;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Workshop;
use App\Models\Employment;
use App\Livewire\Forms\UserForm;
use Carbon\Carbon;

class Index extends Component
{
    use WithPagination;

    // Filters
    public $status = '';
    public $role   = '';
    public $q      = '';
    public $perPage = 10;

    // Modal control
    public bool $showDetail = false;
    public bool $showEdit   = false;
    public bool $showDelete = false;
    public bool $showReset  = false;
    public bool $showCreate = false;

    // Forms
    public UserForm $form;

    // Selected user + form fields
    public ?User $selectedUser = null;
    public string $newPassword = '';
    public string $confirmPassword = '';

    // Dropdown options
    public array $statusOptions = [
        ''         => 'Semua Status',
        'active'   => 'Aktif',
        'inactive' => 'Nonaktif',
        'pending'  => 'Menunggu Verifikasi',
    ];

    public array $roleOptions = [
        ''            => 'Semua Role',
        'superadmin'  => 'Super Admin',
        'admin'       => 'Admin',
        'owner'       => 'Owner',
        'mechanic'    => 'Mekanik',
    ];

    // Validation rules for password reset
    protected $rules = [
        'newPassword'     => 'required|min:8|same:confirmPassword',
        'confirmPassword' => 'required|min:8',
    ];

    // Reset ALL modal state
    public function mount(): void
    {
        $this->resetModal();
    }

    protected function resetModal(): void
    {
        $this->showReset  = false;
        $this->showDetail = false;
        $this->showEdit   = false;
        $this->showDelete = false;
        $this->showCreate = false;

        $this->selectedUser = null;
        $this->newPassword = '';
        $this->confirmPassword = '';
        $this->form->reset();
    }

    public function render()
    {
        $now = Carbon::now();
        $lastWeek = $now->copy()->subWeek();

        // Statistik
        $totalUsers = User::count();
       $lastWeekUsers = User::whereBetween('created_at', [$lastWeek->startOfWeek(), $lastWeek->endOfWeek()])
            ->count();
        $growthUsers = $this->calculateGrowth($totalUsers, $lastWeekUsers);

        $totalPending = User::whereNull('email_verified_at')->count();
        $lastWeekPending = User::whereNull('email_verified_at')
            ->whereBetween('created_at', [$lastWeek->startOfWeek(), $lastWeek->endOfWeek()])
            ->count();
        $growthPending = $this->calculateGrowth($totalPending, $lastWeekPending);

        $totalActive = User::whereNotNull('email_verified_at')->count();
        $lastWeekActive = User::whereNotNull('email_verified_at')
            ->whereBetween('created_at', [$lastWeek->startOfWeek(), $lastWeek->endOfWeek()])
            ->count();
        $growthActive = $this->calculateGrowth($totalActive, $lastWeekActive);

        // Tidak aktif
        $totalInactive = DB::getSchemaBuilder()->hasColumn('users', 'last_login_at')
            ? User::whereNull('last_login_at')->count()
            : 0;

        $lastWeekInactive = DB::getSchemaBuilder()->hasColumn('users', 'last_login_at')
            ? User::whereNull('last_login_at')
                ->whereBetween('created_at', [$lastWeek->startOfWeek(), $lastWeek->endOfWeek()])
                ->count()
            : 0;

        $growthInactive = $this->calculateGrowth($totalInactive, $lastWeekInactive);

        // Role counter
        $roleCounts = DB::table('model_has_roles')
            ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->select('roles.name as role', DB::raw('COUNT(model_has_roles.model_id) as total'))
            ->groupBy('roles.name')
            ->pluck('total', 'role')
            ->toArray();

        $totalMechanic = $roleCounts['mechanic'] ?? 0;
        $totalOwner    = $roleCounts['owner'] ?? 0;

        // Query users with workshop relationship
        $users = User::query()
            ->when($this->q, fn($q) =>
                $q->where('name', 'like', "%{$this->q}%")
                  ->orWhere('email', 'like', "%{$this->q}%"))
            ->when($this->role, fn($q) =>
                $q->whereHas('roles', fn($r) => $r->where('name', $this->role)))
            ->with(['employment.workshop', 'roles'])
            ->paginate($this->perPage);

        // Get workshops for dropdown
        $workshops = Workshop::select('id', 'name')->get();

        return view('livewire.admin.users.index', compact(
            'users',
            'workshops',
            'totalUsers',
            'totalPending',
            'totalActive',
            'totalInactive',
            'totalMechanic',
            'totalOwner',
            'growthUsers',
            'growthPending',
            'growthActive',
            'growthInactive'
        ))->layout('layouts.app');
    }

    // ==========================
    // MODAL OPENERS
    // ==========================

    public function create()
    {
        $this->form->reset();
        $this->showCreate = true;
    }

    public function view($id)
    {
        $this->selectedUser = User::with(['employment.workshop', 'roles'])->findOrFail($id);
        $this->showDetail   = true;
    }

    public function edit($id)
    {
        $this->selectedUser = User::with(['employment.workshop', 'roles'])->findOrFail($id);
        $this->form->setUser($this->selectedUser);
        $this->showEdit = true;
    }

    public function delete($id)
    {
        $this->selectedUser = User::with(['employment.workshop', 'roles'])->findOrFail($id);
        $this->showDelete   = true;
    }

    public function resetPassword($id)
    {
        $this->selectedUser = User::findOrFail($id);
        $this->showReset    = true;
    }

    // ==========================
    // CRUD ACTIONS
    // ==========================

    public function createUser()
    {
        $this->form->validate();

        DB::beginTransaction();
        try {
            // Create user
            $user = User::create([
                'name' => $this->form->name,
                'email' => $this->form->email,
                'password' => Hash::make($this->form->password),
            ]);

            // Assign role
            $user->assignRole($this->form->role);

            // Create employment if workshop is selected and role requires it
            if ($this->form->workshop_id && in_array($this->form->role, ['mechanic', 'owner'])) {
                Employment::create([
                    'user_uuid' => $user->id,
                    'workshop_uuid' => $this->form->workshop_id,
                    'status' => 'active',
                    'code' => 'EMP' . time(),
                ]);
            }

            DB::commit();

            $this->resetModal();
            session()->flash('message', 'Pengguna berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function updateUser()
    {
        $this->form->validate();

        DB::beginTransaction();
        try {
            if (!$this->selectedUser) {
                throw new \Exception('User not found');
            }

            // Update user
            $this->selectedUser->update([
                'name' => $this->form->name,
                'email' => $this->form->email,
            ]);

            // Update password if provided
            if (!empty($this->form->password)) {
                $this->selectedUser->update([
                    'password' => Hash::make($this->form->password),
                ]);
            }

            // Update role
            $this->selectedUser->syncRoles([$this->form->role]);

            // Update or create employment
            if ($this->form->workshop_id && in_array($this->form->role, ['mechanic', 'owner'])) {
                Employment::updateOrCreate(
                    ['user_uuid' => $this->selectedUser->id],
                    [
                        'workshop_uuid' => $this->form->workshop_id,
                        'status' => 'active',
                        'code' => 'EMP' . time(),
                    ]
                );
            } else {
                // Remove employment if role doesn't require workshop
                Employment::where('user_uuid', $this->selectedUser->id)->delete();
            }

            DB::commit();

            $this->resetModal();
            session()->flash('message', 'Pengguna berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function confirmDelete()
    {
        DB::beginTransaction();
        try {
            if (!$this->selectedUser) {
                throw new \Exception('User not found');
            }

            // Delete employment first (if exists)
            Employment::where('user_uuid', $this->selectedUser->id)->delete();

            // Delete user
            $this->selectedUser->delete();

            DB::commit();

            $this->resetModal();
            session()->flash('message', 'Pengguna berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    // ==========================
    // RESET PASSWORD ACTION
    // ==========================

    public function updatePassword()
    {
        $this->validate();

        if ($this->selectedUser) {
            $this->selectedUser->update([
                'password' => Hash::make($this->newPassword),
            ]);
        }

        $this->resetModal();
        session()->flash('message', 'Password berhasil diubah.');
    }

    // ==========================
    // UTIL
    // ==========================

    public function getUserStatus(User $user): string
    {
        // Superadmin and Owner always active
        if ($user->hasRole('superadmin') || $user->hasRole('owner')) {
            return 'Aktif';
        }

        // Check employment status for other roles
        $employment = $user->employment;
        if ($employment) {
            return $employment->status === 'active' ? 'Aktif' : 'Tidak Aktif';
        }

        return 'Tidak Ada Data';
    }

    private function calculateGrowth($current, $previous)
    {
        if ($previous == 0) {
            return $current > 0 ? '+100%' : '+0%';
        }

        $growth = (($current - $previous) / $previous) * 100;
        $sign = $growth >= 0 ? '+' : '';

        return $sign . number_format($growth, 0) . '%';
    }
}
