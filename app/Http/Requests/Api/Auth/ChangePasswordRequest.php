<?php

namespace App\Http\Requests\Api\Auth;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ChangePasswordRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * Route ini sudah dilindungi auth:sanctum, jadi return true.
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
        /** @var User $user */
        $user = $this->user();
        $mustChange = (bool) $user->must_change_password;

        $rules = [
            'new_password' => ['required', 'string', 'min:8', 'confirmed'],
        ];

        // Jika user tidak *dipaksa* ganti password,
        // maka kita wajibkan 'current_password' dan validasi.
        if (! $mustChange) {
            $rules['current_password'] = [
                'required',
                'string',
                'min:6',
                // Pindahkan logika Hash::check ke sini
                function ($attribute, $value, $fail) use ($user) {
                    if (! Hash::check($value, $user->password)) {
                        $fail('Password saat ini salah.');
                    }
                },
            ];
        }

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'new_password.confirmed' => 'Konfirmasi password baru tidak cocok.',
        ];
    }
}
