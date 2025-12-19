<?php

namespace App\Http\Requests\Api\Service;

use App\Models\Service;
use Illuminate\Foundation\Http\FormRequest;

class UpdateServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Service|null $service */
        $service = $this->route('service');

        return $service
            ? $this->user()?->can('update', $service) ?? false
            : false;
    }

    public function rules(): array
    {
        return [
            'workshop_uuid'    => 'sometimes|required|uuid|exists:workshops,id',
            'name'             => 'sometimes|required|string|max:255|min:3',
            'description'      => 'sometimes|nullable|string|max:1000',
            'category_service' => 'sometimes|nullable|string|max:100',
            'price'            => 'sometimes|nullable|numeric|min:0|max:999999999',
            'scheduled_date'   => 'sometimes|required|date',
            'estimated_time'   => 'sometimes|nullable|date|after_or_equal:scheduled_date',
            'status'           => 'sometimes|required|in:pending,in progress,completed,menunggu pembayaran,lunas',
            'customer_uuid'    => 'sometimes|nullable|uuid|exists:customers,id',
            'vehicle_uuid'     => 'sometimes|nullable|uuid|exists:vehicles,id',
            'mechanic_uuid'    => 'sometimes|nullable|uuid|exists:employments,id',
            'reason'           => 'sometimes|nullable|string|max:500',
            'feedback_mechanic'=> 'sometimes|nullable|string|max:500',
            'accepted_at'      => 'prohibited',
            'completed_at'     => 'prohibited',
        ];
    }

    /**
     * Prepare data for validation - sanitize inputs
     */
    protected function prepareForValidation()
    {
        $fieldsToTrim = ['name', 'description', 'category_service', 'reason', 'feedback_mechanic'];
        $sanitized = [];
        
        foreach ($fieldsToTrim as $field) {
            if ($this->has($field)) {
                $sanitized[$field] = trim($this->$field ?? '');
            }
        }
        
        if (!empty($sanitized)) {
            $this->merge($sanitized);
        }
    }

    /**
     * Custom error messages
     */
    public function messages(): array
    {
        return [
            'workshop_uuid.exists' => 'Workshop tidak ditemukan.',
            'name.min' => 'Nama service minimal 3 karakter.',
            'name.max' => 'Nama service maksimal 255 karakter.',
            'estimated_time.after_or_equal' => 'Estimasi waktu selesai harus setelah tanggal service.',
            'price.numeric' => 'Harga harus berupa angka.',
            'price.min' => 'Harga tidak boleh negatif.',
        ];
    }
}
