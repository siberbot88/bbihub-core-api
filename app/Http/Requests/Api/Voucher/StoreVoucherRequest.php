<?php

namespace App\Http\Requests\Api\Voucher;

use Illuminate\Foundation\Http\FormRequest;

class StoreVoucherRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'workshop_uuid' => 'required|uuid|exists:workshops,uuid',
            'code_voucher' => 'required|string|max:255|unique:vouchers,code_voucher',
            'title' => 'required|string|max:255',
            'discount_value' => 'required|numeric|min:0',
            'quota' => 'required|integer|min:0',
            'min_transaction' => 'required|numeric|min:0',
            'valid_from' => 'required|date|after_or_equal:today',
            'valid_until' => 'required|date|after:valid_from',
            'is_active' => 'sometimes|boolean',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ];
    }
}
