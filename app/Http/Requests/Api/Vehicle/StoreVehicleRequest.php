<?php

namespace App\Http\Requests\Api\Vehicle;

use Illuminate\Foundation\Http\FormRequest;

class StoreVehicleRequest extends FormRequest
{
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
            'customer_uuid' => ['required', 'uuid', 'exists:customers,id'],
            'name'          => ['required', 'string', 'min:2', 'max:255'],
            'type'          => ['required', 'string', 'max:100'],
            'brand'         => ['required', 'string', 'min:2', 'max:100'],
            'category'      => ['required', 'string', 'max:100'],
            'model'         => ['required', 'string', 'min:2', 'max:100'],
            'year'          => ['required', 'integer', 'min:1900', 'max:' . (date('Y') + 1)],
            'color'         => ['required', 'string', 'max:50'],
            'plate_number'  => ['required', 'string', 'max:20', 'unique:vehicles,plate_number'],
            'odometer'      => ['required', 'integer', 'min:0'],
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => trim($this->name ?? ''),
            'brand' => trim($this->brand ?? ''),
            'model' => trim($this->model ?? ''),
            'plate_number' => strtoupper(trim($this->plate_number ?? '')),
            'color' => trim($this->color ?? ''),
        ]);
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'customer_uuid.required' => 'Customer harus dipilih',
            'customer_uuid.exists' => 'Customer tidak ditemukan',
            'name.required' => 'Nama kendaraan wajib diisi',
            'name.min' => 'Nama kendaraan minimal 2 karakter',
            'brand.required' => 'Merek kendaraan wajib diisi',
            'brand.min' => 'Merek minimal 2 karakter',
            'model.required' => 'Model kendaraan wajib diisi',
            'model.min' => 'Model minimal 2 karakter',
            'year.required' => 'Tahun kendaraan wajib diisi',
            'year.integer' => 'Tahun harus berupa angka',
            'year.min' => 'Tahun kendaraan tidak valid',
            'year.max' => 'Tahun kendaraan tidak valid',
            'plate_number.required' => 'Nomor polisi wajib diisi',
            'plate_number.unique' => 'Nomor polisi sudah terdaftar',
            'odometer.required' => 'Odometer wajib diisi',
            'odometer.integer' => 'Odometer harus berupa angka',
        ];
    }
}
