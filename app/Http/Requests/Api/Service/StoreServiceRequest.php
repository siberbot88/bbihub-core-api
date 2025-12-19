<?php

namespace App\Http\Requests\Api\Service;

use App\Models\Service;
use Illuminate\Foundation\Http\FormRequest;

class StoreServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Policy: hanya admin (ServicePolicy@create)
        return $this->user()?->can('create', Service::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'workshop_uuid'    => 'required|uuid|exists:workshops,id',
            'name'             => 'required|string|max:255|min:3',
            'description'      => 'nullable|string|max:1000',
            'category_service' => 'nullable|string|max:100',
            'price'            => 'nullable|numeric|min:0|max:999999999',
            'scheduled_date'   => 'required|date|after_or_equal:today',
            'estimated_time'   => 'nullable|date|after_or_equal:scheduled_date',
            'status'           => 'nullable|in:pending,in progress,completed,menunggu pembayaran,lunas',
            'customer_uuid'    => 'required|uuid|exists:customers,id',
            'vehicle_uuid'     => 'required|uuid|exists:vehicles,id',
            'mechanic_uuid'    => 'nullable|uuid|exists:employments,id',
            'reason'           => 'nullable|string|max:500',
            'feedback_mechanic'=> 'nullable|string|max:500',
            'accepted_at'      => 'prohibited',
            'completed_at'     => 'prohibited',
        ];
    }

    /**
     * Prepare data for validation - sanitize inputs
     */
    protected function prepareForValidation()
    {
        $this->merge([
            'name' => trim($this->name ?? ''),
            'description' => trim($this->description ?? ''),
            'category_service' => trim($this->category_service ?? ''),
            'reason' => trim($this->reason ?? ''),
            'feedback_mechanic' => trim($this->feedback_mechanic ?? ''),
        ]);
    }

    /**
     * Custom error messages
     */
    public function messages(): array
    {
        return [
            'workshop_uuid.required' => 'Workshop harus dipilih.',
            'workshop_uuid.exists' => 'Workshop tidak ditemukan.',
            'name.required' => 'Nama service harus diisi.',
            'name.min' => 'Nama service minimal 3 karakter.',
            'name.max' => 'Nama service maksimal 255 karakter.',
            'scheduled_date.required' => 'Tanggal service harus diisi.',
            'scheduled_date.after_or_equal' => 'Tanggal service tidak boleh di masa lalu.',
            'estimated_time.after_or_equal' => 'Estimasi waktu selesai harus setelah tanggal service.',
            'customer_uuid.required' => 'Customer harus dipilih.',
            'customer_uuid.exists' => 'Customer tidak ditemukan.',
            'vehicle_uuid.required' => 'Kendaraan harus dipilih.',
            'vehicle_uuid.exists' => 'Kendaraan tidak ditemukan.',
            'price.numeric' => 'Harga harus berupa angka.',
            'price.min' => 'Harga tidak boleh negatif.',
        ];
    }
}
