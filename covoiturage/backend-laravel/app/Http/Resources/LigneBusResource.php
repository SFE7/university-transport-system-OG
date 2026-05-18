<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LigneBusResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'color' => $this->color,
            'is_active' => $this->is_active,
            'next_departure' => $this->next_departure ?? null,
            'arrets' => $this->whenLoaded('arrets'),
            'horaires' => $this->whenLoaded('horaires'),
            'created_at' => $this->created_at,
        ];
    }
}
