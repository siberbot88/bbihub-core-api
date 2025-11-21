<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VoucherResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'code_voucher'    => $this->code_voucher,
            'title'           => $this->title,

            'workshop_uuid'   => $this->workshop_uuid,
            'workshop' => $this->whenLoaded('workshop', fn () => [
                'uuid' => $this->workshop->uuid,
            ]),
            'discount_value'  => (float) $this->discount_value,
            'quota'           => $this->quota,
            'min_transaction' => (float) $this->min_transaction,

            'valid_from'      => optional($this->valid_from)->toDateString(),
            'valid_until'     => optional($this->valid_until)->toDateString(),
            'is_active'       => (bool) $this->is_active,
            'status'          => $this->status,

            'image'           => $this->image,
            'image_url'       => $this->image_url,

            'created_at'      => optional($this->created_at)?->toIso8601String(),
            'updated_at'      => optional($this->updated_at)?->toIso8601String(),
        ];
    }
}
