<?php

namespace App\Http\Requests\Api\Voucher;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateVoucherRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $voucherId = $this->route('voucher');

        return [
            'workshop_uuid' => 'sometimes|required|uuid|exists:workshops,uuid',
            'code_voucher' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('vouchers')->ignore($voucherId),
            ],
            'title' => 'sometimes|required|string|max:255',
            'discount_value' => 'sometimes|required|numeric|min:0',
            'quota' => 'sometimes|required|integer|min:0',
            'min_transaction' => 'sometimes|required|numeric|min:0',
            'valid_from' => 'sometimes|required|date',
            'valid_until' => 'sometimes|required|date|after:valid_from',
            'is_active' => 'sometimes|boolean',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ];
    }
}
