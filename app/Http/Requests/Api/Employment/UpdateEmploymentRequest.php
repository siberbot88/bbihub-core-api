<?php

namespace App\Http\Requests\Api\Employment;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEmploymentRequest extends FormRequest
{
    private const ALLOWED_ROLES = ['admin', 'mechanic'];

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $employee = $this->route('employee');
        return $employee && $employee->workshop->user_uuid === $this->user()->id;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $userId = $this->route('employee')->user_uuid;

        return [
            'name'      => ['sometimes', 'required', 'string', 'max:255'],
            'username'  => ['sometimes', 'required', 'string', 'max:255',
                Rule::unique('users', 'username')->ignore($userId, 'id'),
            ],
            'email'     => ['sometimes', 'required', 'string', 'email', 'max:255',
                Rule::unique('users', 'email')->ignore($userId, 'id'),
            ],
            'password'  => ['nullable', 'string', 'min:8', 'confirmed'],

            'role'      => ['sometimes', 'required', 'string', Rule::in(self::ALLOWED_ROLES)],

            'specialist'=> ['nullable', 'string', 'max:255'],
            'jobdesk'   => ['nullable', 'string'],
            'status'    => ['nullable', Rule::in(['active', 'inactive'])],
        ];
    }

    public function messages(): array
    {
        return [
            'role.in' => 'Role harus salah satu dari: admin.',
        ];
    }
}
