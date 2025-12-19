<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateWorkshopRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization handled by middleware/policy
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'sometimes|string|min:3|max:100',
            'address' => 'sometimes|string|max:255',
            'phone' => 'sometimes|string|max:20',
            'opening_hours' => 'sometimes|json',
            'description' => 'sometimes|string|max:1000',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('name')) {
            $this->merge(['name' => trim($this->name)]);
        }
        if ($this->has('address')) {
            $this->merge(['address' => trim($this->address)]);
        }
        if ($this->has('phone')) {
            $this->merge(['phone' => trim($this->phone)]);
        }
        if ($this->has('description')) {
            $this->merge(['description' => trim($this->description)]);
        }
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.min' => 'Nama bengkel minimal 3 karakter',
            'name.max' => 'Nama bengkel maksimal 100 karakter',
            'address.max' => 'Alamat maksimal 255 karakter',
            'phone.max' => 'Nomor telepon maksimal 20 karakter',
            'opening_hours.json' => 'Format jam buka tidak valid',
            'description.max' => 'Deskripsi maksimal 1000 karakter',
        ];
    }
}
