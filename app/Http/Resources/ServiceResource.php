<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ServiceResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'code' => $this->code ,
            'name' => $this->name,
            'description' => $this->description,
            'price' => (float) $this->price,
            'scheduled_date' => $this->scheduled_date,
            'estimated_time' => $this->estimated_time,
            'status' => $this->status,
            'acceptance_status' => $this->acceptance_status,
            'workshop' => $this->whenLoaded('workshop'),
            'customer' => $this->whenLoaded('customer'),
            'vehicle' => $this->whenLoaded('vehicle'),
            'mechanic' => $this->whenLoaded('mechanic'),
            'items' => $this->whenLoaded('items'),
            'log' => $this->whenLoaded('log'),
            'task' => $this->whenLoaded('task'),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
