<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'            => $this->id,
            'service_uuid'  => $this->service_uuid,
            'customer_uuid' => $this->customer_uuid,
            'workshop_uuid' => $this->workshop_uuid,
            'admin_uuid'    => $this->admin_uuid,
            'mechanic_uuid' => $this->mechanic_uuid,
            'status'        => $this->status,
            'amount'        => $this->amount,
            'payment_method'=> $this->payment_method,

            // relasi service (opsional)
            'service' => $this->whenLoaded('service', function () {
                return [
                    'id'   => $this->service->id,
                    'code' => $this->service->code,
                    'name' => $this->service->name,
                    'status' => $this->service->status,
                ];
            }),

            // relasi items
            'items' => TransactionItemResource::collection(
                $this->whenLoaded('items')
            ),

            'created_at'    => optional($this->created_at)->toIso8601String(),
            'updated_at'    => optional($this->updated_at)->toIso8601String(),
        ];
    }
}
