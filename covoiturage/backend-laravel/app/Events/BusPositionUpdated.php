<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\BusPosition;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BusPositionUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly BusPosition $position
    ) {}

    public function broadcastOn(): Channel
    {
        return new Channel('bus.' . $this->position->chauffeur_id);
    }

    public function broadcastWith(): array
    {
        return [
            'chauffeur_id' => $this->position->chauffeur_id,
            'latitude'     => $this->position->latitude,
            'longitude'    => $this->position->longitude,
            'updated_at'   => $this->position->updated_at,
        ];
    }
}
