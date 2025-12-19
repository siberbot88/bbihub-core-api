<?php

namespace App\Livewire\Admin\Users;

use App\Models\User;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Illuminate\Http\RedirectResponse;

#[Title('Edit Pengguna')]
#[Layout('layouts.app')]
class Edit extends Component
{
    public User $user;

    public string $name  = '';
    public string $email = '';
    public ?string $status = null; // sesuaikan dengan kolom di tabel users (optional)

    public function mount(User $user): void
    {
        $this->user   = $user;
        $this->name   = $user->name ?? '';
        $this->email  = $user->email ?? '';
        // kalau tidak ada kolom status di tabel, boleh hapus baris ini
        $this->status = $user->status ?? null;
    }


    public function save(): RedirectResponse
    {
        $data = $this->validate([
            'name'   => ['required', 'string', 'max:255'],
            'email'  => ['required', 'email', 'max:255'],
            // kalau tidak ada kolom status, hapus rule ini
            'status' => ['nullable', 'string'],
        ]);

        $this->user->update($data);

        session()->flash('success', 'Pengguna berhasil diperbarui.');

        // Balik ke daftar user (atau ke data center, terserah kamu)
        return redirect()->route('admin.users.index');
        // atau kalau mau ke data center:
        // return redirect()->route('admin.data-center', ['cat' => 'users']);
    }

    public function render()
    {
        return view('livewire.admin.users.edit');
    }
}
