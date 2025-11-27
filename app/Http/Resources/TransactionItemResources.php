<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TransactionItemResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'             => $this->id,
            'name'           => $this->name,
            'service_type'   => $this->service_type,   // misal: jasa / sparepart
            'price'          => $this->price,
            'quantity'       => $this->quantity,
            'subtotal'       => $this->subtotal,
            'service_uuid'   => $this->service_uuid ?? null,
            'transaction_id' => $this->transaction_uuid ?? null,
        ];
    }
}
