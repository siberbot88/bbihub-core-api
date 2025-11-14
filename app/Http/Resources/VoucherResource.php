<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class VoucherResource extends JsonResource
{
    /**
     * Transform resource menjadi array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $status = 'active';
        $now = Carbon::now();

        if (!$this->is_active) {
            $status = 'inactive';
        } elseif ($this->valid_until < $now) {
            $status = 'expired';
        } elseif ($this->valid_from > $now) {
            $status = 'scheduled';
        }

        return [
            'id' => $this->id,
            'workshop_uuid' => $this->workshop_uuid,
            'code_voucher' => $this->code_voucher,
            'title' => $this->title,
            'discount_value' => $this->discount_value,
            'quota' => $this->quota,
            'min_transaction' => $this->min_transaction,
            'valid_from' => $this->valid_from->toDateString(),
            'valid_until' => $this->valid_until->toDateString(),
            'is_active' => $this->is_active,
            'image_url' => $this->image_url,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            // Data tambahan untuk Flutter:
            'status' => $status,
            // 'remaining_quota' => $this->calculateRemainingQuota(), // Lihat catatan di bawah
        ];
    }
}
