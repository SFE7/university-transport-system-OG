<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReservationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'membre_id' => $this->membre_id,
            'trajet_id' => $this->trajet_id,
            'status' => $this->status,
            'trajet' => new TrajetResource($this->whenLoaded('trajet')),
            'created_at' => $this->created_at,
        ];
    }
}
