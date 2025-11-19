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
            'name'          => ['required', 'string', 'max:255'],
            'type'          => ['required', 'string', 'max:100'],
            'brand'         => ['required', 'string', 'max:100'],
            'category'      => ['required', 'string', 'max:100'],
            'model'         => ['required', 'string', 'max:100'],
            'year'          => ['required', 'string', 'max:10'],
            'color'         => ['required', 'string', 'max:50'],
            'plate_number'  => ['required', 'string', 'max:50', 'unique:vehicles,plate_number'],
            'odometer'      => ['required', 'string', 'max:50'],
        ];
    }
}
