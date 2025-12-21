<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'             => $this->id,
            'service_uuid'   => $this->service_uuid,
            'customer_uuid'  => $this->customer_uuid,
            'workshop_uuid'  => $this->workshop_uuid,
            'admin_uuid'     => $this->admin_uuid,
            'mechanic_uuid'  => $this->mechanic_uuid,
            'status'         => $this->status,
            'amount'         => $this->amount,
            'payment_method' => $this->payment_method,

            'service'        => $this->whenLoaded('service', function () {
                return [
                    'id'               => $this->service->id,
                    'code'             => $this->service->code,
                    'name'             => $this->service->name,
                    'description'      => $this->service->description,
                    'category_service' => $this->service->category_service,
                    'scheduled_date'   => optional($this->service->scheduled_date)->toIso8601String(),
                    'status'           => $this->service->status,

                    'customer'         => $this->when($this->service->relationLoaded('customer') && $this->service->customer, function () {
                        return [
                            'id'      => $this->service->customer->id,
                            'name'    => $this->service->customer->name,
                            'phone'   => $this->service->customer->phone,
                            'email'   => $this->service->customer->email,
                            'address' => $this->service->customer->address,
                        ];
                    }),

                    'vehicle'          => $this->when($this->service->relationLoaded('vehicle') && $this->service->vehicle, function () {
                        return [
                            'id'           => $this->service->vehicle->id,
                            'plate_number' => $this->service->vehicle->plate_number,
                            'brand'        => $this->service->vehicle->brand,
                            'model'        => $this->service->vehicle->model,
                            'name'         => $this->service->vehicle->name,
                            'year'         => $this->service->vehicle->year,
                            'color'        => $this->service->vehicle->color,
                            'category'     => $this->service->vehicle->category,
                        ];
                    }),

                    'workshop'         => $this->when($this->service->relationLoaded('workshop') && $this->service->workshop, function () {
                        return [
                            'id'   => $this->service->workshop->id,
                            'name' => $this->service->workshop->name,
                        ];
                    }),
                ];
            }),

            'items'          => TransactionItemResource::collection(
                $this->whenLoaded('items')
            ),

            'mechanic'       => $this->whenLoaded('mechanic', function () {
                return [
                    'id'   => $this->mechanic->id,
                    'name' => optional($this->mechanic->user)->name,
                ];
            }),

            'invoice'        => $this->whenLoaded('invoice', function () {
                return [
                    'id'       => $this->invoice->id,
                    'code'     => $this->invoice->code,
                    'amount'   => $this->invoice->amount,
                    'due_date' => optional($this->invoice->due_date)->toIso8601String(),
                    'paid_at'  => optional($this->invoice->paid_at)->toIso8601String(),
                    'status'   => $this->invoice->paid_at ? 'paid' : 'unpaid',
                ];
            }),

            'created_at'     => optional($this->created_at)->toIso8601String(),
            'updated_at'     => optional($this->updated_at)->toIso8601String(),
        ];
    }
}
