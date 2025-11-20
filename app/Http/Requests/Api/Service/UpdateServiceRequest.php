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
            'workshop_uuid'  => 'sometimes|required|uuid|exists:workshops,id',
            'name'           => 'sometimes|required|string|max:255',
            'description'    => 'sometimes|nullable|string',
            'price'          => 'sometimes|nullable|numeric|min:0|max:999999.99',
            'scheduled_date' => 'sometimes|required|date',
            'estimated_time' => 'sometimes|nullable|date',
            'status'         => 'sometimes|required|in:pending,accept,in progress,completed,cancelled',
            'customer_uuid'  => 'sometimes|nullable|uuid|exists:customers,id',
            'vehicle_uuid'   => 'sometimes|nullable|uuid|exists:vehicles,id',
            'mechanic_uuid'  => 'sometimes|nullable|uuid|exists:employments,id',
            'reason'         => 'nullable|string',
            'feedback_mechanic'=> 'nullable|string',
        ];
    }
}
