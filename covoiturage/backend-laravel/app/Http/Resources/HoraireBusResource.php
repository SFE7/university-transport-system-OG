<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HoraireBusResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'ligne_bus_id' => $this->ligne_bus_id,
            'chauffeur_id' => $this->chauffeur_id,
            'departure_time' => $this->departure_time,
            'days' => $this->days,
            'is_active' => $this->is_active,
            'chauffeur' => new MembreResource($this->whenLoaded('chauffeur')),
            'ligne' => new LigneBusResource($this->whenLoaded('ligne')),
            'created_at' => $this->created_at,
        ];
    }
}
