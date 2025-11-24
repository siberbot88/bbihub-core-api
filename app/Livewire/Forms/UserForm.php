<?php

namespace App\Livewire\Forms;

use Livewire\Attributes\Validate;
use Livewire\Form;

class UserForm extends Form
{
    #[Validate('required|string|max:255')]
    public string $name = '';

    #[Validate('required|email|max:255|unique:users,email')]
    public string $email = '';

    #[Validate('required|string|min:8')]
    public string $password = '';

    #[Validate('nullable|exists:workshops,id')]
    public ?string $workshop_id = null;

    #[Validate('required|in:admin,owner,mechanic,superadmin')]
    public string $role = '';

    public ?string $user_id = null;

    public function setUser($user)
    {
        $this->user_id = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->role = $user->roles->first()?->name ?? '';
        $this->workshop_id = $user->employment?->workshop_uuid ?? null;
        
        // Password is not set for edit
        $this->password = '';
    }

    public function reset(...$properties)
    {
        // If specific properties are provided, reset only those
        if (count($properties) > 0) {
            parent::reset(...$properties);
            return;
        }

        // Otherwise reset all form properties
        $this->name = '';
        $this->email = '';
        $this->password = '';
        $this->workshop_id = null;
        $this->role = '';
        $this->user_id = null;
    }

    public function rules()
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'role' => 'required|in:admin,owner,mechanic,superadmin',
            'workshop_id' => 'nullable|exists:workshops,id',
        ];

        // For create, password is required
        if (!$this->user_id) {
            $rules['password'] = 'required|string|min:8';
            $rules['email'] .= '|unique:users,email';
        } else {
            // For edit, password is optional
            $rules['password'] = 'nullable|string|min:8';
            $rules['email'] .= '|unique:users,email,' . $this->user_id;
        }

        return $rules;
    }
}
