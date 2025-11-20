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
            'name'             => 'required|string|max:255',
            'description'      => 'nullable|string',
            'category_service' => 'nullable|string',
            'price'            => 'nullable|numeric|min:0|max:999999.99',
            'scheduled_date'   => 'required|date',
            'estimated_time'   => 'nullable|date',
            'status'           => 'nullable|in:pending,accept,in progress,completed,cancelled',
            'customer_uuid'    => 'nullable|uuid|exists:customers,id',
            'vehicle_uuid'     => 'nullable|uuid|exists:vehicles,id',
            'mechanic_uuid'    => 'nullable|uuid|exists:employments,id',
            'reason'           => 'nullable|string',
            'feedback_mechanic'=> 'nullable|string',
            'accepted_at'      => 'prohibited',
            'completed_at'     => 'prohibited',
        ];
    }
}
