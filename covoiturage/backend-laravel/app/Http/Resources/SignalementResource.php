<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SignalementResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'membre' => new MembreResource($this->whenLoaded('membre')),
            'conducteur' => new MembreResource($this->whenLoaded('conducteur')),
            'trajet' => new TrajetResource($this->whenLoaded('trajet')),
            'reporter' => new MembreResource($this->whenLoaded('membre')),
            'reported' => new MembreResource($this->whenLoaded('conducteur')),
            'reason' => $this->reason,
            'description' => $this->description,
            'status' => $this->status,
            'created_at' => $this->created_at,
        ];
    }
}
