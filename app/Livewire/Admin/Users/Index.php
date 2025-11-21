<?php

namespace App\Livewire\Admin\Users;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
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
        ''         => 'Semua Role',
        'admin'    => 'Admin',
        'owner'    => 'Owner',
        'mechanic' => 'Mekanik',
    ];

    // Validation rules
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

        $this->selectedUser = null;
        $this->newPassword = '';
        $this->confirmPassword = '';
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

        // Query users
        $users = User::query()
            ->when($this->q, fn($q) =>
                $q->where('name', 'like', "%{$this->q}%")
                  ->orWhere('email', 'like', "%{$this->q}%"))
            ->when($this->role, fn($q) =>
                $q->whereHas('roles', fn($r) => $r->where('name', $this->role)))
            ->with('roles')
            ->paginate($this->perPage);

        return view('livewire.admin.users.index', compact(
            'users',
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

    public function view($id)
    {
        $this->selectedUser = User::findOrFail($id);
        $this->showDetail   = true;
    }

    public function edit($id)
    {
        $this->selectedUser = User::findOrFail($id);
        $this->showEdit     = true;
    }

    public function delete($id)
    {
        $this->selectedUser = User::findOrFail($id);
        $this->showDelete   = true;
    }

    public function resetPassword($id)
    {
        $this->selectedUser = User::findOrFail($id);
        $this->showReset    = true;
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
