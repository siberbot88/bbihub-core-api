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

    public $showDetail = false;
    public $showEdit = false;
    public $showReset = false;
    public $showDelete = false;

    public $selectedUser = null;
    public $newPassword;
    public $confirmPassword;

    public $statusOptions = [
        '' => 'Semua Status',
        'active' => 'Aktif',
        'inactive' => 'Nonaktif',
        'pending' => 'Menunggu Verifikasi',
    ];

    public $roleOptions = [
        '' => 'Semua Role',
        'admin' => 'Admin',
        'owner' => 'Owner',
        'mechanic' => 'Mekanik',
    ];

    public function render()
    {
        $now = Carbon::now();
        $lastWeek = $now->copy()->subWeek();

        // Total pengguna
        $totalUsers = User::count();
        $lastWeekUsers = User::whereBetween('created_at', [$lastWeek->startOfWeek(), $lastWeek->endOfWeek()])->count();
        $growthUsers = $this->calculateGrowth($totalUsers, $lastWeekUsers);

        // Menunggu verifikasi
        $totalPending = User::whereNull('email_verified_at')->count();
        $lastWeekPending = User::whereNull('email_verified_at')
            ->whereBetween('created_at', [$lastWeek->startOfWeek(), $lastWeek->endOfWeek()])
            ->count();
        $growthPending = $this->calculateGrowth($totalPending, $lastWeekPending);

        // Akun aktif
        $totalActive = User::whereNotNull('email_verified_at')->count();
        $lastWeekActive = User::whereNotNull('email_verified_at')
            ->whereBetween('created_at', [$lastWeek->startOfWeek(), $lastWeek->endOfWeek()])
            ->count();
        $growthActive = $this->calculateGrowth($totalActive, $lastWeekActive);

        // Akun tidak aktif
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

        // Hitung berdasarkan role
        $roleCounts = DB::table('model_has_roles')
            ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->select('roles.name as role', DB::raw('COUNT(model_has_roles.model_id) as total'))
            ->groupBy('roles.name')
            ->pluck('total', 'role')
            ->toArray();

        $totalMechanic = $roleCounts['mechanic'] ?? 0;
        $totalOwner = $roleCounts['owner'] ?? 0;

        // Ambil daftar user
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

    public function view($id)
    {
        $this->selectedUser = User::findOrFail($id);
        $this->dispatchBrowserEvent('open-modal', ['detail' => 'detail-user']);
    }

    public function edit($id)
    {
        $this->selectedUser = User::findOrFail($id);
        $this->dispatchBrowserEvent('open-modal', ['detail' => 'edit-user']);
    }

    public function resetPassword($id)
    {
        $this->selectedUser = User::findOrFail($id);
        $this->dispatchBrowserEvent('open-modal', ['detail' => 'reset-user']);
    }

    public function delete($id)
    {
        $this->selectedUser = User::findOrFail($id);
        $this->dispatchBrowserEvent('open-modal', ['detail' => 'delete-user']);
    }

    public function updateUser()
    {
        $this->selectedUser->save();
        $this->dispatchBrowserEvent('close-modal', ['detail' => 'edit-user']);
    }

    public function updatePassword()
    {
        $this->validate([
            'newPassword' => 'required|min:6|same:confirmPassword',
        ]);

        $this->selectedUser->update([
            'password' => Hash::make($this->newPassword),
        ]);

        $this->dispatchBrowserEvent('close-modal', ['detail' => 'reset-user']);
        $this->newPassword = $this->confirmPassword = '';
    }

    public function confirmDelete($id)
    {
        User::findOrFail($id)->delete();
        $this->dispatchBrowserEvent('close-modal', ['detail' => 'delete-user']);
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
