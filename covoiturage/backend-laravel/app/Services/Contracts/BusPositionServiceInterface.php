<?php

declare(strict_types=1);

namespace App\Services\Contracts;

use App\Models\BusPosition;
use App\Models\Membre;
use Illuminate\Support\Collection;

interface BusPositionServiceInterface
{
    public function updatePosition(Membre $chauffeur, array $data): BusPosition;

    public function stopSharing(Membre $chauffeur): BusPosition;

    public function getActivePositions(): Collection;

    public function checkProximityAlerts(BusPosition $position): void;
}
