<?php

namespace App\Livewire\Forms;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Livewire\Form;

class UserForm extends Form
{
    public ?string $user_id = null;

    public string $name = '';
    public string $username = '';
    public string $email = '';
    public string $password = '';
    public string $role = '';
    public ?string $workshop_id = null;

    public function setUser($user): void
    {
        $this->user_id = (string) $user->id;
        $this->name = (string) ($user->name ?? '');
        $this->username = (string) ($user->username ?? '');
        $this->email = (string) ($user->email ?? '');
        $this->role = (string) ($user->roles->first()?->name ?? '');
        $this->workshop_id = $user->employment?->workshop_uuid ? (string) $user->employment->workshop_uuid : null;
        $this->password = '';
    }

    public function reset(...$properties): void
    {
        if (count($properties) > 0) {
            parent::reset(...$properties);
            return;
        }

        $this->user_id = null;
        $this->name = '';
        $this->username = '';
        $this->email = '';
        $this->password = '';
        $this->role = '';
        $this->workshop_id = null;
    }

    // Wajib ada biar Livewire tidak MissingRulesException
    public function rules(): array
    {
        $isCreate = empty($this->user_id);

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:50'],
            'email' => ['required', 'email', 'max:255'],
            'role' => ['required', Rule::in(['admin', 'owner', 'mechanic', 'superadmin'])],
            'workshop_id' => ['nullable', Rule::exists('workshops', 'id')],
        ];

        if ($isCreate) {
            $rules['password'] = ['required', 'string', 'min:8'];
            $rules['username'][] = Rule::unique('users', 'username');
            $rules['email'][] = Rule::unique('users', 'email');
        } else {
            $rules['password'] = ['nullable', 'string', 'min:8'];
            $rules['username'][] = Rule::unique('users', 'username')->ignore($this->user_id);
            $rules['email'][] = Rule::unique('users', 'email')->ignore($this->user_id);
        }

        // owner & mechanic wajib workshop (sesuaikan kalau admin juga wajib)
        if (in_array($this->role, ['owner', 'mechanic'], true)) {
            $rules['workshop_id'] = ['required', Rule::exists('workshops', 'id')];
        }

        if ($this->role === 'superadmin') {
            $rules['workshop_id'] = ['nullable'];
        }

        return $rules;
    }

    public function validateCreate(): void
    {
        $this->user_id = null;
        Validator::make($this->toArray(), $this->rules(), [], $this->attributes())->validate();
    }

    public function validateUpdate(string $userId): void
    {
        $this->user_id = $userId;
        Validator::make($this->toArray(), $this->rules(), [], $this->attributes())->validate();
    }

    public function attributes(): array
    {
        return [
            'name' => 'Nama',
            'username' => 'Username',
            'email' => 'Email',
            'password' => 'Password',
            'role' => 'Role',
            'workshop_id' => 'Bengkel',
        ];
    }
}
