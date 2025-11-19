<?php

namespace App\Http\Requests\Api\Voucher;

use App\Models\Voucher;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateVoucherRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Voucher|null $voucher */
        $voucher = $this->route('voucher');
        $user    = $this->user();

        if (! $voucher || ! $user) {
            return false;
        }

        $newWorkshopUuid = $this->input('workshop_uuid');

        if ($newWorkshopUuid && $newWorkshopUuid !== $voucher->workshop_uuid) {
            if (! $user->can('create', [Voucher::class, (string) $newWorkshopUuid])) {
                return false;
            }
        }
        return $user->can('update', $voucher);
    }

    public function rules(): array
    {
        /** @var Voucher|null $voucher */
        $voucher = $this->route('voucher');

        return [
            'workshop_uuid'   => 'sometimes|required|uuid|exists:workshops,id,prohibited',
            'code_voucher'    => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('vouchers', 'code_voucher')->ignore($voucher?->id),
            ],
            'title'           => 'sometimes|required|string|max:255',
            'discount_value'  => 'sometimes|required|numeric|min:0',
            'quota'           => 'sometimes|required|integer|min:0',
            'min_transaction' => 'sometimes|required|numeric|min:0',
            'valid_from'      => 'sometimes|required|date',
            'valid_until'     => 'sometimes|required|date|after:valid_from',
            'is_active'       => 'sometimes|boolean',
            'image'           => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ];
    }
}
