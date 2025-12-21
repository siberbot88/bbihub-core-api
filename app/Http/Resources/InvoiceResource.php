<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'               => $this->id,
            'code'             => $this->code,
            'amount'           => $this->amount,
            'due_date'         => optional($this->due_date)->toIso8601String(),
            'paid_at'          => optional($this->paid_at)->toIso8601String(),
            'status'           => $this->paid_at ? 'paid' : 'unpaid',

            'transaction'      => $this->whenLoaded('transaction', function () {
                return [
                    'id'             => $this->transaction->id,
                    'status'         => $this->transaction->status,
                    'payment_method' => $this->transaction->payment_method,
                    'amount'         => $this->transaction->amount,

                    'service'        => $this->when($this->transaction->relationLoaded('service') && $this->transaction->service, function () {
                        $service = $this->transaction->service;
                        return [
                            'id'               => $service->id,
                            'code'             => $service->code,
                            'name'             => $service->name,
                            'description'      => $service->description,
                            'category_service' => $service->category_service,
                            'scheduled_date'   => optional($service->scheduled_date)->toIso8601String(),

                            'customer'         => $this->when($service->relationLoaded('customer') && $service->customer, function () use ($service) {
                                return [
                                    'id'      => $service->customer->id,
                                    'name'    => $service->customer->name,
                                    'phone'   => $service->customer->phone,
                                    'email'   => $service->customer->email,
                                    'address' => $service->customer->address,
                                ];
                            }),

                            'vehicle'          => $this->when($service->relationLoaded('vehicle') && $service->vehicle, function () use ($service) {
                                return [
                                    'id'           => $service->vehicle->id,
                                    'plate_number' => $service->vehicle->plate_number,
                                    'brand'        => $service->vehicle->brand,
                                    'model'        => $service->vehicle->model,
                                    'name'         => $service->vehicle->name,
                                    'year'         => $service->vehicle->year,
                                    'color'        => $service->vehicle->color,
                                    'category'     => $service->vehicle->category,
                                ];
                            }),

                            'workshop'         => $this->when($service->relationLoaded('workshop') && $service->workshop, function () use ($service) {
                                return [
                                    'id'   => $service->workshop->id,
                                    'name' => $service->workshop->name,
                                ];
                            }),
                        ];
                    }),

                    'items'          => $this->when($this->transaction->relationLoaded('items'), function () {
                        return $this->transaction->items->map(function ($item) {
                            return [
                                'id'           => $item->id,
                                'name'         => $item->name,
                                'service_type' => $item->service_type,
                                'price'        => $item->price,
                                'quantity'     => $item->quantity,
                                'subtotal'     => $item->subtotal,
                            ];
                        });
                    }),

                    'mechanic'       => $this->when($this->transaction->relationLoaded('mechanic') && $this->transaction->mechanic, function () {
                        return [
                            'id'   => $this->transaction->mechanic->id,
                            'name' => optional($this->transaction->mechanic->user)->name,
                        ];
                    }),
                ];
            }),

            'created_at'       => optional($this->created_at)->toIso8601String(),
            'updated_at'       => optional($this->updated_at)->toIso8601String(),
        ];
    }
}
