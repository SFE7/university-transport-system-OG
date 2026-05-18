<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BusPositionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'chauffeur_id' => $this->chauffeur_id,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'is_sharing' => $this->is_sharing,
            'chauffeur' => new MembreResource($this->whenLoaded('chauffeur')),
            'updated_at' => $this->updated_at,
        ];
    }
}
