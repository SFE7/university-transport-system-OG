<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AvisResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'reviewer_id' => $this->reviewer_id,
            'conducteur_id' => $this->conducteur_id,
            'trajet_id' => $this->trajet_id,
            'rating' => $this->rating,
            'comment' => $this->comment,
            'reviewer' => new MembreResource($this->whenLoaded('reviewer')),
            'created_at' => $this->created_at,
        ];
    }
}
