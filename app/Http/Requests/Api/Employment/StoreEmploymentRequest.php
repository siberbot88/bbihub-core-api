<?php

namespace App\Http\Requests\Api\Employment;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEmploymentRequest extends FormRequest
{
    private const ALLOWED_ROLES = ['admin', 'mechanic'];

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Users (tanpa input password)
            'name'          => ['required', 'string', 'max:255'],
            'username'      => ['required', 'string', 'max:255', 'unique:users,username'],
            'email'         => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'photo'         => ['nullable', 'string', 'url'],
            'role'          => ['required', 'string', Rule::in(self::ALLOWED_ROLES)],
            'workshop_uuid' => [
                'required',
                'uuid',
                Rule::exists('workshops', 'id')
                    ->where(fn($q) => $q->where('user_uuid', $this->user()->id)),
            ],
            'specialist'    => ['nullable', 'string', 'max:255'],
            'jobdesk'       => ['nullable', 'string'],
            'status'        => ['nullable', Rule::in(['active', 'inactive'])],
        ];
    }

    public function messages(): array
    {
        return [
            'role.in' => 'Role harus salah satu dari: admin, mechanic/teknisi/technician.',
            'workshop_uuid.exists' => 'Workshop yang dipilih tidak valid atau bukan milik Anda.',
        ];
    }
}
