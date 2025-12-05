<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionItemResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'name'         => $this->name,
            'service_type' => $this->service_type,
            'price'        => $this->price,
            'quantity'     => $this->quantity,
            'subtotal'     => $this->subtotal,

            'service'      => $this->whenLoaded('service', function () {
                return [
                    'id'    => $this->service->id,
                    'code'  => $this->service->code,
                    'name'  => $this->service->name,
                ];
            }),
        ];
    }
}
