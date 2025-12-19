<?php

namespace App\Livewire\Admin\DataCenter;

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use App\Models\User;
use App\Models\Workshop;
use App\Models\Employment;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

#[Layout('layouts.app')]
class Create extends Component
{
    public string $category = 'users';

    public array $categoryOptions = [
        'users'      => 'Pengguna',
        'workshops'  => 'Bengkel',
        'promotions' => 'Promosi',
    ];

    // Role and lookup lists
    public ?string $role = null;
    public array $roles = [];

    // User fields
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public ?string $workshop_id = null;
    public array $workshops = [];
    public ?string $specialist = null;

    // Workshop fields
    public string $workshop_name = '';
    public string $workshop_code = '';

    protected function rules(): array
    {
        return match ($this->category) {
            'workshops' => [
                'workshop_name' => 'required|string|max:255',
                'workshop_code' => 'required|string|max:50|unique:workshops,code',
            ],
            default => $this->userRules(),
        };
    }

    protected function userRules(): array
    {
        $base = [
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'role'     => 'required|string',
        ];

        if (in_array($this->role, ['mechanic', 'owner'])) {
            $base['workshop_id'] = 'required|string';
        }

        if ($this->role === 'mechanic') {
            $base['specialist'] = 'required|string|max:255';
        }

        return $base;
    }

    public function mount(): void
    {
        $this->category = request()->query('category', $this->category);

        // load only allowed roles for dashboard
        try {
            $allowed = ['super-admin', 'superadmin', 'owner', 'admin'];
            $this->roles = Role::whereIn('name', $allowed)->orderBy('name')->get()->map(function ($r) {
                return ['name' => $r->name, 'guard' => $r->guard_name];
            })->toArray();
        } catch (\Throwable $e) {
            $this->roles = [];
        }

        // load workshops for optional assignment
        try {
            $this->workshops = Workshop::orderBy('name')->get()->map(function ($w) {
                return ['id' => $w->id, 'name' => $w->name];
            })->toArray();
        } catch (\Throwable $e) {
            $this->workshops = [];
        }
    }

    public function save()
    {
        $this->validate();

        if ($this->category === 'workshops') {
            Workshop::create([
                'name' => $this->workshop_name,
                'code' => $this->workshop_code,
                'user_uuid' => auth()->id(),
            ]);

            session()->flash('message', 'Bengkel berhasil ditambahkan.');
            return redirect()->route('admin.data-center');
        }

        // generate a username (DB requires it)
        $base = Str::slug($this->name ?: explode('@', $this->email)[0]);
        if (empty($base)) {
            $base = 'user';
        }
        $username = $base;
        $tries = 0;
        while (User::where('username', $username)->exists() && $tries < 20) {
            $username = $base . '-' . rand(10, 99);
            $tries++;
        }

        // create user
        $user = User::create([
            'name' => $this->name,
            'username' => $username,
            'email' => $this->email,
            'password' => Hash::make($this->password),
        ]);

        if ($user && $this->role) {
            try {
                $user->assignRole($this->role);
            } catch (\Throwable $e) {
                // ignore assignment failure
            }
        }

        if ($user && $this->workshop_id) {
            Employment::create([
                'user_uuid' => $user->id,
                'workshop_uuid' => $this->workshop_id,
                'specialist' => $this->specialist,
                'status' => 'active',
            ]);
        }

        session()->flash('message', 'Pengguna berhasil ditambahkan.');
        return redirect()->route('admin.data-center');
    }

    public function render()
    {
        return view('livewire.admin.data-center.create');
    }
}
