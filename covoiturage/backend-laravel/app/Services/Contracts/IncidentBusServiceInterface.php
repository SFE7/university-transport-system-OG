<?php

declare(strict_types=1);

namespace App\Services\Contracts;

use App\Models\IncidentBus;
use App\Models\Membre;
use Illuminate\Support\Collection;

interface IncidentBusServiceInterface
{
    public function getOne(int $id): IncidentBus;

    public function getAll(): Collection;

    public function create(array $data, Membre $actor): IncidentBus;

    public function resolve(IncidentBus $incident, Membre $actor): IncidentBus;

    public function delete(IncidentBus $incident, Membre $actor): void;
}
