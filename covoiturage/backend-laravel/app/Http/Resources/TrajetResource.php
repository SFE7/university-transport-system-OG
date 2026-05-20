<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

use App\Http\Resources\ReservationResource;

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
            'car_category' => $this->car_category,
            'car_model' => $this->car_model,
            'car_photo_url' => $this->car_photo_url ?? ($this->conducteur?->vehicule?->photo_url ?? null),
            'status' => $this->status,
            'membre_id' => $this->membre_id,
            'conducteur' => new MembreResource($this->whenLoaded('conducteur')),
            'reservations' => ReservationResource::collection($this->whenLoaded('reservations')),
            'created_at' => $this->created_at,
        ];
    }
}
