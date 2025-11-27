<?php

namespace App\Http\Requests\Api\Workshop;

use Illuminate\Foundation\Http\FormRequest;

class UpdateWorkshopRequest extends FormRequest
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
            'name' => 'sometimes|string|max:255',
            'photo' => 'sometimes|image|max:2048', // Max 2MB
            'opening_time' => 'sometimes|date_format:H:i:s', // Format H:i:s as per DB
            'closing_time' => 'sometimes|date_format:H:i:s',
            'operational_days' => 'sometimes|string',
            'is_active' => 'sometimes|boolean',
            'description' => 'sometimes|string', // Mapped from 'information'
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation()
    {
        // Map 'jam buka' input if necessary, but assuming frontend sends correct keys or we map them here.
        // If frontend sends 'information' instead of 'description', we can map it.
        if ($this->has('information')) {
            $this->merge([
                'description' => $this->input('information'),
            ]);
        }
        
        // Ensure time format is correct if sent as H:i
        if ($this->has('opening_time') && strlen($this->input('opening_time')) == 5) {
             $this->merge(['opening_time' => $this->input('opening_time') . ':00']);
        }
        if ($this->has('closing_time') && strlen($this->input('closing_time')) == 5) {
             $this->merge(['closing_time' => $this->input('closing_time') . ':00']);
        }
    }
}
