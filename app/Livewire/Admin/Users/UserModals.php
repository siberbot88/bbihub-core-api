<?php

namespace App\Livewire\Admin\Users;

use App\Livewire\Forms\UserForm;
use App\Models\Employment;
use App\Models\User;
use App\Models\Workshop;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Livewire\Attributes\On;
use Livewire\Component;

class UserModals extends Component
{
    // Modal control
    public bool $showDetail = false;
    public bool $showEdit   = false;
    public bool $showDelete = false;
    public bool $showReset  = false;
    public bool $showCreate = false;

    public ?User $selectedUser = null;

    public UserForm $form;

    public string $newPassword = '';
    public string $confirmPassword = '';

    /** @var \Illuminate\Support\Collection<int, Workshop> */
    public $workshops;

    protected function rules(): array
    {
        return [
            'newPassword'     => ['required', 'min:8', 'same:confirmPassword'],
            'confirmPassword' => ['required', 'min:8'],
        ];
    }

    public function mount(): void
    {
        $this->workshops = Workshop::select('id', 'name')->orderBy('name')->get();
        $this->resetModal();
    }

    public function resetModal(): void
    {
        $this->showReset  = false;
        $this->showDetail = false;
        $this->showEdit   = false;
        $this->showDelete = false;
        $this->showCreate = false;

        $this->selectedUser = null;

        $this->newPassword = '';
        $this->confirmPassword = '';

        if (isset($this->form)) {
            $this->form->reset();
        }

        $this->resetErrorBag();
        $this->resetValidation();
    }

    // ==========================
    // EVENT LISTENERS
    // ==========================
    #[On('user:create')]
    public function openCreate(): void
    {
        $this->resetModal();
        $this->showCreate = true;
    }

    #[On('user:view')]
    public function openView(string $id): void
    {
        $this->resetModal();
        $this->selectedUser = User::with(['employment.workshop', 'roles'])->findOrFail($id);
        $this->showDetail   = true;
    }

    #[On('user:edit')]
    public function openEdit(string $id): void
    {
        $this->resetModal();
        $this->selectedUser = User::with(['employment.workshop', 'roles'])->findOrFail($id);
        $this->form->setUser($this->selectedUser);
        $this->showEdit = true;
    }

    #[On('user:delete')]
    public function openDelete(string $id): void
    {
        $this->resetModal();
        $this->selectedUser = User::with(['employment.workshop', 'roles'])->findOrFail($id);
        $this->showDelete   = true;
    }

    #[On('user:reset-password')]
    public function openResetPassword(string $id): void
    {
        $this->resetModal();
        $this->selectedUser = User::findOrFail($id);
        $this->showReset    = true;
    }

    // ==========================
    // CRUD ACTIONS
    // ==========================
    public function createUser(): void
    {
        // 1) rapihin input biar gak ada spasi & null aneh
        $this->sanitizeForm();

        // 2) kalau username kosong, auto generate (biar tidak crash)
        $this->ensureUsernameForCreate();

        // 3) validasi (UserForm harus punya rule username)
        $this->form->validateCreate();

        DB::beginTransaction();
        try {
            $user = User::create([
                'name'     => $this->form->name,
                'username' => $this->form->username,
                'email'    => $this->form->email,
                'password' => Hash::make($this->form->password),
            ]);

            if (!empty($this->form->role)) {
                $user->assignRole($this->form->role);
            }

            $this->syncEmployment($user, $this->form->role, $this->form->workshop_id);

            DB::commit();

            $this->resetModal();
            session()->flash('message', 'Pengguna berhasil ditambahkan.');
            $this->dispatch('users:refresh');
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);
            session()->flash('error', $this->friendlyDbError($e));
        }
    }

    public function updateUser(): void
    {
        if (!$this->selectedUser) {
            session()->flash('error', 'User tidak ditemukan.');
            return;
        }

        $this->sanitizeForm();

        // kalau username kosong saat update, generate juga (opsional tapi aman)
        $this->ensureUsernameForUpdate($this->selectedUser->id);

        $this->form->validateUpdate($this->selectedUser->id);

        DB::beginTransaction();
        try {
            $this->selectedUser->update([
                'name'     => $this->form->name,
                'username' => $this->form->username,
                'email'    => $this->form->email,
            ]);

            if (!empty($this->form->password)) {
                $this->selectedUser->update([
                    'password' => Hash::make($this->form->password),
                ]);
            }

            if (!empty($this->form->role)) {
                $this->selectedUser->syncRoles([$this->form->role]);
            } else {
                $this->selectedUser->syncRoles([]);
            }

            $this->syncEmployment($this->selectedUser, $this->form->role, $this->form->workshop_id);

            DB::commit();

            $this->resetModal();
            session()->flash('message', 'Pengguna berhasil diperbarui.');
            $this->dispatch('users:refresh');
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);
            session()->flash('error', $this->friendlyDbError($e));
        }
    }

    public function confirmDelete(): void
    {
        if (!$this->selectedUser) {
            session()->flash('error', 'User tidak ditemukan.');
            return;
        }

        DB::beginTransaction();
        try {
            Employment::where('user_uuid', $this->selectedUser->id)->delete();
            $this->selectedUser->delete();

            DB::commit();

            $this->resetModal();
            session()->flash('message', 'Pengguna berhasil dihapus.');
            $this->dispatch('users:refresh');
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);
            session()->flash('error', 'Terjadi kesalahan saat menghapus pengguna.');
        }
    }

    public function updatePassword(): void
    {
        $this->validate();

        if (!$this->selectedUser) {
            session()->flash('error', 'User tidak ditemukan.');
            return;
        }

        $this->selectedUser->update([
            'password' => Hash::make($this->newPassword),
        ]);

        $this->resetModal();
        session()->flash('message', 'Password berhasil diubah.');
        $this->dispatch('users:refresh');
    }

    // ==========================
    // Helpers
    // ==========================
    private function sanitizeForm(): void
    {
        // trim string biar tidak ada spasi
        $this->form->name = trim((string) $this->form->name);
        $this->form->email = trim((string) $this->form->email);
        $this->form->username = trim((string) $this->form->username);
        $this->form->role = $this->form->role ? trim((string) $this->form->role) : null;
        $this->form->workshop_id = $this->form->workshop_id ?: null;

        // email lowercase (biar unique konsisten)
        if ($this->form->email) {
            $this->form->email = Str::lower($this->form->email);
        }
    }

    private function ensureUsernameForCreate(): void
    {
        if ($this->form->username !== '') {
            return;
        }

        // base dari name
        $base = Str::slug($this->form->name ?: 'user');

        // cari yang unik
        $candidate = $base;
        $i = 1;

        while (User::where('username', $candidate)->exists()) {
            $candidate = $base . '-' . $i;
            $i++;
            if ($i > 2000) {
                // fallback random
                $candidate = $base . '-' . Str::lower(Str::random(6));
                break;
            }
        }

        $this->form->username = $candidate;
    }

    private function ensureUsernameForUpdate(string $ignoreUserId): void
    {
        if ($this->form->username !== '') {
            return;
        }

        // kalau kosong, generate dari name tapi ignore user saat ini
        $base = Str::slug($this->form->name ?: 'user');
        $candidate = $base;
        $i = 1;

        while (
            User::where('username', $candidate)
                ->where('id', '!=', $ignoreUserId)
                ->exists()
        ) {
            $candidate = $base . '-' . $i;
            $i++;
            if ($i > 2000) {
                $candidate = $base . '-' . Str::lower(Str::random(6));
                break;
            }
        }

        $this->form->username = $candidate;
    }

    private function syncEmployment(User $user, ?string $role, $workshopId): void
    {
        // biasanya owner, mechanic, admin butuh workshop (sesuaikan rules kamu)
        $needsEmployment = in_array($role, ['mechanic', 'owner', 'admin'], true);

        if ($needsEmployment) {
            if (empty($workshopId)) {
                // biar jelas errornya (daripada silent)
                throw new \RuntimeException('Role ini membutuhkan bengkel, tetapi bengkel belum dipilih.');
            }

            Employment::updateOrCreate(
                ['user_uuid' => $user->id],
                [
                    'workshop_uuid' => $workshopId,
                    'status'        => 'active',
                    'code'          => Employment::where('user_uuid', $user->id)->value('code') ?? ('EMP-' . $user->id),
                ]
            );
        } else {
            Employment::where('user_uuid', $user->id)->delete();
        }
    }

    private function friendlyDbError(\Throwable $e): string
    {
        $msg = $e->getMessage();

        if (str_contains($msg, "Field 'username' doesn't have a default value")) {
            return "Username wajib diisi. (Field username kosong / tidak terkirim ke server)";
        }

        if (str_contains($msg, "Duplicate entry") && str_contains($msg, "users_username_unique")) {
            return "Username sudah dipakai. Silakan gunakan username lain.";
        }

        if (str_contains($msg, "Duplicate entry") && str_contains($msg, "users_email_unique")) {
            return "Email sudah dipakai. Silakan gunakan email lain.";
        }

        return 'Terjadi kesalahan saat menyimpan data.';
    }

    public function render()
    {
        return view('livewire.admin.users.user-modals', [
            'workshops' => $this->workshops,
        ]);
    }
}
