<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

use App\Http\Resources\VehiculeResource;

class MembreResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $data = [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'role' => $this->role,
            'account_type' => $this->account_type,
            'is_active' => $this->is_active,
            'is_banned' => $this->is_banned,
            'has_verified_documents' => $this->has_verified_documents,
            'created_at' => $this->created_at,
        ];

        if ($this->role === 'conducteur' && $this->relationLoaded('vehicule')) {
            $data['vehicule'] = new VehiculeResource($this->vehicule);
        }

        return $data;
    }
}

