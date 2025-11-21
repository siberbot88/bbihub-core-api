<?php

namespace App\Livewire\Admin\Users;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\User;
use App\Models\Workshop;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class Create extends Component
{
    use WithFileUploads;

    // User fields
    public $photo;
    public $username;
    public $name;
    public $email;
    public $phone;
    public $password;
    public $role;

    // Workshop fields (untuk owner / preview admin & mekanik)
    public $nama_bengkel;
    public $alamat;
    public $city;
    public $province;
    public $country;

    // Auto-complete workshop untuk admin/mekanik
    public $bengkelOptions = [];
    public $selected_workshop_id;

    public function updatedRole()
    {
        // reset data workshop tiap ganti role
        $this->nama_bengkel = null;
        $this->alamat = null;
        $this->city = null;
        $this->province = null;
        $this->country = null;
        $this->selected_workshop_id = null;
        $this->bengkelOptions = [];
    }

    public function updatedNamaBengkel()
    {
        // auto-complete hanya admin/mekanik
        if (in_array($this->role, ['admin', 'mechanic']) && strlen((string) $this->nama_bengkel) >= 2) {
            $this->bengkelOptions = Workshop::where('name', 'like', '%'.$this->nama_bengkel.'%')
                ->orderBy('name')
                ->limit(10)
                ->get();
        } else {
            $this->bengkelOptions = [];
        }
    }

    public function pilihBengkel(string $id): void
    {
        $workshop = Workshop::find($id);
        if (! $workshop) {
            return;
        }

        $this->selected_workshop_id = $workshop->id;
        $this->nama_bengkel = $workshop->name;
        $this->alamat       = $workshop->address;
        $this->city         = $workshop->city;
        $this->province     = $workshop->province;
        $this->country      = $workshop->country;
        $this->bengkelOptions = [];
    }

    public function save()
    {
        // Base rules (semua wajib)
        $rules = [
            'photo'    => 'required|image|max:2048',
            'username' => 'required|string|max:50|unique:users,username',
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'phone'    => 'required|string|max:20',
            'password' => 'required|min:6',
            'role'     => 'required|in:superadmin,owner,admin,mechanic',
        ];

        // Semua role selain superadmin wajib punya workshop
        if ($this->role !== 'superadmin') {
            $rules['nama_bengkel'] = 'required|string|max:255';

            if ($this->role === 'owner') {
                // Owner isi data workshop sendiri → wajib
                $rules['alamat']   = 'required|string|max:500';
                $rules['city']     = 'required|string|max:100';
                $rules['province'] = 'required|string|max:100';
                $rules['country']  = 'required|string|max:100';
            } else {
                // admin / mekanik harus pilih workshop dari list
                $rules['selected_workshop_id'] = [
                    'required',
                    Rule::exists('workshops', 'id'),
                ];
            }
        }

        $this->validate($rules);

        $photoPath = $this->photo->store('users', 'public');

        // simpan user
        $user = User::create([
            'username' => $this->username,
            'name'     => $this->name,
            'email'    => $this->email,
            'phone'    => $this->phone,
            'password' => Hash::make($this->password),
            'photo'    => $photoPath, // ganti kalau nama kolom foto berbeda
        ]);

        // kalau owner → buat Workshop baru
        if ($this->role === 'owner') {
            Workshop::create([
                'user_uuid'   => $user->id, // sesuaikan kalau user pakai UUID lain
                'name'        => $this->nama_bengkel,
                'address'     => $this->alamat,
                'phone'       => $this->phone,
                'email'       => $this->email,
                'city'        => $this->city,
                'province'    => $this->province,
                'country'     => $this->country,
                'is_active'   => true,
            ]);
        }

        // TODO: relasi admin/mekanik ke workshop (Employment / pivot dll)
        // if (in_array($this->role, ['admin', 'mechanic'])) {
        //     Employment::create([...]);
        // }

        $user->assignRole($this->role);

        session()->flash('success', 'Pengguna berhasil ditambahkan.');
        return redirect()->route('admin.users');
    }

    public function render()
    {
        return view('livewire.admin.users.create')
            ->layout('layouts.app');
    }
}
