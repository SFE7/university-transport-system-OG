<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DocumentSoumisResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'membre' => new MembreResource($this->whenLoaded('membre')),
            'type' => $this->type,
            'file_path' => $this->file_path,
            'url' => $this->file_path ? $request->getSchemeAndHttpHost() . '/storage/' . ltrim($this->file_path, '/') : null,
            'status' => $this->status,
            'rejection_reason' => $this->rejection_reason,
            'reviewed_by' => $this->reviewed_by,
            'reviewed_at' => $this->reviewed_at,
            'created_at' => $this->created_at,
        ];
    }
}
