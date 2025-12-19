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
            'name'          => ['sometimes', 'string', 'min:2', 'max:255'],
            'type'          => ['sometimes', 'string', 'max:100'],
            'category'      => ['sometimes', 'string', 'max:100'],
            'brand'         => ['sometimes', 'string', 'min:2', 'max:100'],
            'model'         => ['sometimes', 'string', 'min:2', 'max:100'],
            'year'          => ['sometimes', 'integer', 'min:1900', 'max:' . (date('Y') + 1)],
            'color'         => ['sometimes', 'string', 'max:50'],
            'plate_number'  => [
                'sometimes', 'string', 'max:20',
                Rule::unique('vehicles', 'plate_number')->ignore($vehicleId, 'id'),
            ],
            'odometer'      => ['sometimes', 'integer', 'min:0'],
            'regenerate_code' => ['sometimes', 'boolean'],
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
        if ($this->has('brand')) {
            $this->merge(['brand' => trim($this->brand)]);
        }
        if ($this->has('model')) {
            $this->merge(['model' => trim($this->model)]);
        }
        if ($this->has('plate_number')) {
            $this->merge(['plate_number' => strtoupper(trim($this->plate_number))]);
        }
        if ($this->has('color')) {
            $this->merge(['color' => trim($this->color)]);
        }
    }
}
