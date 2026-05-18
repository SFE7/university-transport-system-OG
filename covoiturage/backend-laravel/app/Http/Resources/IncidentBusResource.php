<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class IncidentBusResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'ligne_bus_id' => $this->ligne_bus_id,
            'reported_by' => $this->reported_by,
            'type' => $this->type,
            'description' => $this->description,
            'resolved_at' => $this->resolved_at,
            'ligne' => new LigneBusResource($this->whenLoaded('ligne')),
            'reporter' => new MembreResource($this->whenLoaded('reporter')),
            'created_at' => $this->created_at,
        ];
    }
}
