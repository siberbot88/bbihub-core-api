<?php

namespace App\Http\Requests\Api\Transaction;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTransactionRequest extends FormRequest
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
            'payment_method' => 'sometimes|in:cash,transfer,qris,midtrans',
            'amount'         => 'sometimes|numeric|min:0|max:999999999',
            'status'         => 'sometimes|in:pending,process,success,cancelled',
            'notes'          => 'sometimes|string|max:500',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('notes')) {
            $this->merge(['notes' => trim($this->notes)]);
        }
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'payment_method.in' => 'Metode pembayaran tidak valid',
            'amount.numeric' => 'Jumlah harus berupa angka',
            'amount.min' => 'Jumlah tidak boleh negatif',
            'amount.max' => 'Jumlah terlalu besar',
            'status.in' => 'Status tidak valid',
            'notes.max' => 'Catatan maksimal 500 karakter',
        ];
    }
}
