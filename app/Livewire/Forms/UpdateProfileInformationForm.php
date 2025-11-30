<?php

namespace App\Livewire\Forms;

use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Validate;
use Livewire\Form;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class UpdateProfileInformationForm extends Form
{
    public ?User $user = null;

    #[Validate('required|string|max:255')]
    public string $name = '';

    #[Validate('required|string|max:255')]
    public string $username = '';

    #[Validate('required|string|email|max:255')]
    public string $email = '';

    #[Validate('nullable|image|max:2048')]
    public $photo = null;

    public bool $removePhoto = false;

    /**
     * Set the user instance.
     */
    public function setUser(User $user): void
    {
        $this->user = $user;
        $this->name = $user->name;
        $this->username = $user->username;
        $this->email = $user->email;
    }

    /**
     * Get validation rules.
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'username' => [
                'required',
                'string',
                'max:255',
                Rule::unique('users', 'username')->ignore($this->user?->id)
            ],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($this->user?->id)
            ],
            'photo' => ['nullable', 'image', 'max:2048'], // Max 2MB
        ];
    }

    /**
     * Get custom validation messages in Indonesian.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Nama wajib diisi.',
            'name.max' => 'Nama maksimal 255 karakter.',
            'username.required' => 'Username wajib diisi.',
            'username.unique' => 'Username sudah digunakan.',
            'username.max' => 'Username maksimal 255 karakter.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah terdaftar.',
            'email.max' => 'Email maksimal 255 karakter.',
            'photo.image' => 'File harus berupa gambar.',
            'photo.max' => 'Ukuran foto maksimal 2MB.',
        ];
    }

    /**
     * Update the user's profile information.
     */
    public function update(): void
    {
        $this->validate();

        if (!$this->user) {
            throw new \Exception('User tidak ditemukan.');
        }

        // Handle photo upload
        if ($this->photo instanceof TemporaryUploadedFile) {
            // Delete old photo if exists
            if ($this->user->photo) {
                Storage::disk('public')->delete($this->user->photo);
            }

            // Store new photo
            $path = $this->photo->store('avatars', 'public');
            $this->user->photo = $path;
        }

        // Handle photo removal
        if ($this->removePhoto && $this->user->photo) {
            Storage::disk('public')->delete($this->user->photo);
            $this->user->photo = null;
        }

        // Update user information
        $this->user->name = $this->name;
        $this->user->username = $this->username;
        $this->user->email = $this->email;

        // Reset email verification if email changed
        if ($this->user->isDirty('email')) {
            $this->user->email_verified_at = null;
        }

        $this->user->save();

        // Reset photo inputs
        $this->photo = null;
        $this->removePhoto = false;
    }
}
