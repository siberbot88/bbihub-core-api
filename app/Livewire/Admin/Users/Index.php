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
use Livewire\Attributes\On;

class Index extends Component
{
    use WithPagination;

    // Filters
    public $status = '';
    public $role = '';
    public $q = '';
    public $perPage = 10;

    // Dropdown options
    public array $statusOptions = [
        '' => 'Semua Status',
        'active' => 'Aktif',
        'inactive' => 'Nonaktif',
        'pending' => 'Menunggu Verifikasi',
    ];

    public array $roleOptions = [
        '' => 'Semua Role',
        'superadmin' => 'Super Admin',
        'admin' => 'Admin',
        'owner' => 'Owner',
        'mechanic' => 'Mekanik',
    ];

    // Event Listener for refresh
    #[On('users:refresh')]
    public function refresh(): void
    {
        // Just calling this to re-render component
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

        // Active Users Logic: Superadmin/Owner OR has active employment
        $activeUserIds = User::whereHas('roles', function ($q) {
            $q->whereIn('name', ['superadmin', 'owner']);
        })
            ->orWhereHas('employment', function ($q) {
                $q->where('status', 'active');
            })
            ->pluck('id');

        $totalActive = $activeUserIds->count();

        $lastWeekActive = User::whereIn('id', $activeUserIds)
            ->whereBetween('created_at', [$lastWeek->startOfWeek(), $lastWeek->endOfWeek()])
            ->count();

        $growthActive = $this->calculateGrowth($totalActive, $lastWeekActive);

        // Inactive Users (Everything else)
        $totalInactive = $totalUsers - $totalActive;

        // For last week inactive, we count users created last week who are NOT in the active list
        $lastWeekInactive = User::whereNotIn('id', $activeUserIds)
            ->whereBetween('created_at', [$lastWeek->startOfWeek(), $lastWeek->endOfWeek()])
            ->count();

        $growthInactive = $this->calculateGrowth($totalInactive, $lastWeekInactive);

        // Role counter
        $roleCounts = \DB::table('model_has_roles')
            ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->select('roles.name as role', \DB::raw('COUNT(model_has_roles.model_id) as total'))
            ->groupBy('roles.name')
            ->pluck('total', 'role')
            ->toArray();

        $totalMechanic = $roleCounts['mechanic'] ?? 0;
        $totalOwner = $roleCounts['owner'] ?? 0;

        // Query users with workshop relationship
        $users = User::query()
            ->when($this->q, fn($q) =>
                $q->where('name', 'like', "%{$this->q}%")
                    ->orWhere('email', 'like', "%{$this->q}%"))
            ->when($this->role, fn($q) =>
                $q->whereHas('roles', fn($r) => $r->where('name', $this->role)))
            ->with(['employment.workshop', 'roles', 'workshop', 'ownerSubscription.plan'])
            ->paginate($this->perPage);

        // Get workshops for dropdown (Optional if needed for filter, otherwise remove)
        $workshops = Workshop::select('id', 'name')->get();

        return view('livewire.admin.users.index', compact(
            'users',
            'workshops',
            'totalUsers',
            'totalActive',
            'totalInactive',
            'totalMechanic',
            'totalOwner',
            'growthUsers',
            'growthActive',
            'growthInactive'
        ))->layout('layouts.app');
    }

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
