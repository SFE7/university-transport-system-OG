<?php

declare(strict_types=1);

namespace App\Services\Contracts;

use App\Models\LigneBus;
use Illuminate\Support\Collection;

interface LigneBusServiceInterface
{
    public function getAll(): Collection;

    public function getNextDeparture(LigneBus $ligne): ?string;

    public function getOne(int $id): LigneBus;

    public function create(array $data): LigneBus;

    public function update(LigneBus $ligne, array $data): LigneBus;

    public function toggleActive(LigneBus $ligne): LigneBus;

    public function delete(LigneBus $ligne): void;

    public function getSchedules(int $ligneId, ?string $day): Collection;
}
