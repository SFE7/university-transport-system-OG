<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TrajetResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'departure_point' => $this->departure_point,
            'arrival_point' => $this->arrival_point,
            'departure_time' => $this->departure_time,
            'available_seats' => $this->available_seats,
            'status' => $this->status,
            'membre_id' => $this->membre_id,
            'conducteur' => new MembreResource($this->whenLoaded('conducteur')),
            'created_at' => $this->created_at,
        ];
    }
}
