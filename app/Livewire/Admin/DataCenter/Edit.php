<?php

namespace App\Livewire\Admin\DataCenter;

use Livewire\Component;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\Workshop;
use App\Models\Employment;
use Spatie\Permission\Models\Role;

class Edit extends Component
{
    public string $category = 'users';
    public ?string $idParam = null; // id query param

    // shared
    public ?string $role = null;
    public array $roles = [];
    public array $workshops = [];

    // user fields
    public string $name = '';
    public string $email = '';
    public ?string $username = null;
    public ?string $password = null;
    public ?string $workshop_id = null;
    public ?string $specialist = null;

    // workshop fields
    public string $workshop_name = '';
    public string $workshop_code = '';

    protected function rules(): array
    {
        return match ($this->category) {
            'workshops' => [
                'workshop_name' => 'required|string|max:255',
                'workshop_code' => 'required|string|max:50',
            ],
            default => $this->userRules(),
        };
    }

    protected function userRules(): array
    {
        $base = [
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'role' => 'required|string',
        ];

        if (in_array($this->role, ['owner'])) {
            $base['workshop_id'] = 'required|string';
        }

        if ($this->role === 'owner') {
            $base['specialist'] = 'nullable|string|max:255';
        }

        return $base;
    }

    public function mount(): void
    {
        $this->category = request()->query('category', $this->category);
        $this->idParam = request()->query('id');

        // load allowed roles and workshops
        try {
            $allowed = ['super-admin', 'superadmin', 'owner', 'admin'];
            $this->roles = Role::whereIn('name', $allowed)->orderBy('name')->pluck('name')->toArray();
        } catch (\Throwable $e) {
            $this->roles = [];
        }

        try {
            $this->workshops = Workshop::orderBy('name')->get()->map(function ($w) {
                return ['id' => $w->id, 'name' => $w->name];
            })->toArray();
        } catch (\Throwable $e) {
            $this->workshops = [];
        }

        // load model if id provided
        if ($this->idParam) {
            if ($this->category === 'workshops') {
                $w = Workshop::find($this->idParam);
                if ($w) {
                    $this->workshop_name = $w->name;
                    $this->workshop_code = $w->code;
                }
            } else {
                $u = User::find($this->idParam);
                if ($u) {
                    $this->name = $u->name;
                    $this->email = $u->email;
                    $this->username = $u->username ?? null;
                    $this->role = $u->roles()->first()?->name ?? null;
                    $emp = $u->employment()->first();
                    if ($emp) {
                        $this->workshop_id = $emp->workshop_uuid;
                        $this->specialist = $emp->specialist;
                    }
                }
            }
        }
    }

    public function save()
    {
        $this->validate();

        if ($this->category === 'workshops') {
            $w = Workshop::findOrFail($this->idParam);
            $w->update([
                'name' => $this->workshop_name,
                'code' => $this->workshop_code,
            ]);

            session()->flash('message', 'Bengkel berhasil diperbarui.');
            return redirect()->route('admin.data-center');
        }

        $u = User::findOrFail($this->idParam);
        $updates = [
            'name' => $this->name,
            'email' => $this->email,
        ];
        if ($this->password) {
            $updates['password'] = Hash::make($this->password);
        }
        if ($this->username) {
            $updates['username'] = $this->username;
        }
        $u->update($updates);

        // roles
        if ($this->role) {
            try { $u->syncRoles([$this->role]); } catch (\Throwable $e) {}
        }

        // employment
        if ($this->workshop_id) {
            $emp = $u->employment()->first();
            if ($emp) {
                $emp->update(['workshop_uuid' => $this->workshop_id, 'specialist' => $this->specialist]);
            } else {
                Employment::create(['user_uuid' => $u->id, 'workshop_uuid' => $this->workshop_id, 'specialist' => $this->specialist, 'status' => 'active']);
            }
        }

        session()->flash('message', 'Pengguna berhasil diperbarui.');
        return redirect()->route('admin.data-center');
    }

    public function render()
    {
        return view('livewire.admin.data-center.edit');
    }
}
