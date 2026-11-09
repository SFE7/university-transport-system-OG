<?php

declare(strict_types=1);

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BusApproachingAlert implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly int $membreId,
        public readonly string $ligneName,
        public readonly int $minutesAway
    ) {}

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel('membre.' . $this->membreId);
    }

    public function broadcastWith(): array
    {
        return [
            'message'      => "Le bus {$this->ligneName} arrive dans {$this->minutesAway} minutes",
            'minutes_away' => $this->minutesAway,
            'ligne_name'   => $this->ligneName,
        ];
    }
}
