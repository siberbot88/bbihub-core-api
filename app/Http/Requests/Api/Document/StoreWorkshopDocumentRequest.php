<?php

namespace App\Http\Requests\Api\Document;

use App\Models\Workshop;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreWorkshopDocumentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        $workshopUuid = $this->input('workshop_uuid');
        if (!$workshopUuid) {
            return false;
        }

        $workshop = Workshop::find($workshopUuid);
        return $workshop && $workshop->user_uuid === Auth::id();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'workshop_uuid' => 'required|uuid|exists:workshops,id',
            'nib' => 'required|string|max:255',
            'npwp' => 'required|string|max:255',
        ];
    }
}
