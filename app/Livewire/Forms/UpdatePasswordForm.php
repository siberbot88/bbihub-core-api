<?php

namespace App\Livewire\Forms;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Validate;
use Livewire\Form;

class UpdatePasswordForm extends Form
{
    public ?User $user = null;

    #[Validate('required|string')]
    public string $current_password = '';

    #[Validate('required|string|confirmed')]
    public string $password = '';

    public string $password_confirmation = '';

    /**
     * Set the user instance.
     */
    public function setUser(User $user): void
    {
        $this->user = $user;
    }

    /**
     * Get validation rules.
     */
    public function rules(): array
    {
        return [
            'current_password' => ['required', 'string', 'current_password'],
            'password' => ['required', 'string', 'confirmed', Password::defaults()],
        ];
    }

    /**
     * Get custom validation messages in Indonesian.
     */
    public function messages(): array
    {
        return [
            'current_password.required' => 'Password saat ini wajib diisi.',
            'current_password.current_password' => 'Password saat ini tidak sesuai.',
            'password.required' => 'Password baru wajib diisi.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'password.min' => 'Password minimal :min karakter.',
        ];
    }

    /**
     * Update the user's password.
     */
    public function update(): void
    {
        $this->validate();

        if (!$this->user) {
            throw new \Exception('User tidak ditemukan.');
        }

        // Update password
        $this->user->password = Hash::make($this->password);
        $this->user->save();

        // Reset form
        $this->reset();
    }
}
