<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ServiceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'code'             => $this->code,
            'name'             => $this->name,
            'description'      => $this->description,
            'price'            => $this->price,
            'scheduled_date'   => optional($this->scheduled_date)->toIso8601String(),
            'estimated_time'   => optional($this->estimated_time)->toIso8601String(),
            'status'           => $this->status,
            'acceptance_status'=> $this->acceptance_status,

            // kolom di DB: category_service → kirim ke frontend sebagai category_name
            'category_service' => $this->category_service,
            'category_name'    => $this->category_service,

            'accepted_at'      => optional($this->accepted_at)->toIso8601String(),
            'completed_at'     => optional($this->completed_at)->toIso8601String(),

            'workshop' => $this->whenLoaded('workshop', function () {
                return [
                    'id'   => $this->workshop->id,
                    'name' => $this->workshop->name,
                ];
            }),

            'customer' => $this->whenLoaded('customer', function () {
                return [
                    'id'      => $this->customer->id,
                    'name'    => $this->customer->name,
                    'phone'   => $this->customer->phone,
                    'email'   => $this->customer->email,
                    'address' => $this->customer->address,
                ];
            }),

            'vehicle'  => $this->whenLoaded('vehicle', function () {
                return [
                    'id'           => $this->vehicle->id,
                    'plate_number' => $this->vehicle->plate_number,
                    'brand'        => $this->vehicle->brand,
                    'model'        => $this->vehicle->model,
                    'name'         => $this->vehicle->name,
                    'year'         => $this->vehicle->year,
                    'color'        => $this->vehicle->color,
                    'odometer'     => $this->vehicle->odometer,
                ];
            }),

            'mechanic' => $this->whenLoaded('mechanic', function () {
                return [
                    'id'   => $this->mechanic->id,
                    'name' => optional($this->mechanic->user)->name,
                ];
            }),

            'reason'            => $this->reason,
            'feedback_mechanic' => $this->feedback_mechanic,
            'note'              => $this->note,

            'items'             => $this->when(
                $this->relationLoaded('transaction') && $this->transaction && $this->transaction->relationLoaded('items'),
                function () {
                    return $this->transaction->items->map(function ($it) {
                        return [
                            'id'                => $it->id,
                            'name'              => $it->name,
                            'service_type_name' => $it->service_type,
                            'price'             => $it->price,
                            'quantity'          => $it->quantity,
                            'subtotal'          => $it->subtotal,
                        ];
                    });
                }
            ),

            'created_at'        => optional($this->created_at)->toIso8601String(),
            'updated_at'        => optional($this->updated_at)->toIso8601String(),
        ];
    }
}
