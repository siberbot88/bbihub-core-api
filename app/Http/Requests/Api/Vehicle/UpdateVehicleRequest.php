<?php

namespace App\Http\Requests\Api\Vehicle;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateVehicleRequest extends FormRequest
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
        $vehicleId = $this->route('vehicle')->id;

        return [
            'customer_uuid' => ['sometimes', 'uuid', 'exists:customers,id'],
            'name'          => ['sometimes', 'string', 'max:255'],
            'type'          => ['sometimes', 'string', 'max:100'],
            'category'      => ['sometimes', 'string', 'max:100'],
            'brand'         => ['sometimes', 'string', 'max:100'],
            'model'         => ['sometimes', 'string', 'max:100'],
            'year'          => ['sometimes', 'string', 'max:10'],
            'color'         => ['sometimes', 'string', 'max:50'],
            'plate_number'  => [
                'sometimes', 'string', 'max:50',
                Rule::unique('vehicles', 'plate_number')->ignore($vehicleId, 'id'),
            ],
            'odometer'      => ['sometimes', 'string', 'max:50'],
            'regenerate_code' => ['sometimes', 'boolean'],
        ];
    }
}
