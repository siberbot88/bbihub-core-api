<?php

namespace App\Http\Requests\Api\Service;

use Illuminate\Foundation\Http\FormRequest;

class StoreWalkInServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('admin');
    }

    public function rules(): array
    {
        return [
            'workshop_uuid'    => 'required|uuid|exists:workshops,id',
            
            // Customer Data
            'customer_name'    => 'required|string|max:255',
            'customer_phone'   => 'required|string|max:20',
            'customer_email'   => 'nullable|email|max:255',
            
            // Vehicle Data
            'vehicle_plate'    => 'required|string|max:20',
            'vehicle_brand'    => 'nullable|string|max:50',
            'vehicle_model'    => 'nullable|string|max:50',
            'vehicle_year'     => 'nullable|integer|min:1900|max:' . (date('Y') + 1),
            'vehicle_odometer' => 'nullable|integer|min:0',
            
            // Service Data
            'name'             => 'required|string|max:255',
            'category'         => 'required|string|max:100', // Using string for flexibility/mobile input
            'description'      => 'nullable|string',
            'scheduled_date'   => 'required|date', // Usually 'now'
        ];
    }
}
