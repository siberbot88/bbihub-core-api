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

    public $status = '';
    public $role = '';
    public $q = '';
    public $perPage = 10;

    public bool $showDetail = false;
    public bool $showEdit   = false;
    public bool $showDelete = false;

    // RESET PASSWORD modal state
    public bool $showReset = false;

    public ?User $selectedUser = null;
    public string $newPassword = '';
    public string $confirmPassword = '';

    protected $rules = [
        'newPassword'     => 'required|min:8|same:confirmPassword',
        'confirmPassword' => 'required|min:8',
    ];

    public function mount(): void
    {
        $this->resetModal();
    }

    protected function resetModal(): void
    {
        $this->showReset = false;
        $this->showDetail = false;
        $this->showEdit = false;
        $this->showDelete = false;

        $this->selectedUser = null;
        $this->newPassword = '';
        $this->confirmPassword = '';
    }

    public function render()
    {
        $now = Carbon::now();
        $lastWeek = $now->copy()->subWeek();

        $totalUsers = User::count();
        $lastWeekUsers = User::whereBetween('created_at', [$lastWeek->startOfWeek(), $lastWeek->endOfWeek()])->count();
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

        $hasLastLoginColumn = DB::getSchemaBuilder()->hasColumn('users', 'last_login_at');
        if ($hasLastLoginColumn) {
            $totalInactive = User::whereNull('last_login_at')->count();
            $lastWeekInactive = User::whereNull('last_login_at')
                ->whereBetween('created_at', [$lastWeek->startOfWeek(), $lastWeek->endOfWeek()])
                ->count();
        } else {
            $totalInactive = 0;
            $lastWeekInactive = 0;
        }
        $growthInactive = $this->calculateGrowth($totalInactive, $lastWeekInactive);

        $roleCounts = DB::table('model_has_roles')
            ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->select('roles.name as role', DB::raw('COUNT(model_has_roles.model_id) as total'))
            ->groupBy('roles.name')
            ->pluck('total', 'role')
            ->toArray();

        $totalMechanic = $roleCounts['mechanic'] ?? 0;
        $totalOwner    = $roleCounts['owner'] ?? 0;

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
            'totalMechanic',
            'totalOwner',
            'totalInactive',
            'growthUsers',
            'growthPending',
            'growthActive',
            'growthInactive'
        ))->layout('layouts.app');
    }

    // ==== RESET PASSWORD ====

    public function resetPassword($id)
    {
        $this->selectedUser = User::findOrFail($id);
        $this->showReset = true;
    }

    public function updatePassword()
    {
        $this->validate();

        if ($this->selectedUser) {
            $this->selectedUser->update([
                'password' => Hash::make($this->newPassword),
            ]);
        }

        $this->closeResetModal();
        session()->flash('message', 'Password berhasil diubah.');
    }

    public function closeResetModal()
    {
        $this->showReset = false;
        $this->selectedUser = null;
        $this->newPassword = '';
        $this->confirmPassword = '';
    }

    // ==== OTHER MODALS =====

    public function view($id)
    {
        $this->selectedUser = User::findOrFail($id);
        $this->showDetail = true;
    }

    public function edit($id)
    {
        $this->selectedUser = User::findOrFail($id);
        $this->showEdit = true;
    }

    public function delete($id)
    {
        $this->selectedUser = User::findOrFail($id);
        $this->showDelete = true;
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
