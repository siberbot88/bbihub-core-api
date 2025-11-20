<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TransactionItemResources extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'service_uuid' => $this->service_uuid,
            'status' => $this->status,
            'amount' => $this->amount,
            'payment_method' => $this->payment_method,

            'items' => TransactionItemResource::collection(
                $this->whenLoaded('items')
            )
        ];
    }
}
