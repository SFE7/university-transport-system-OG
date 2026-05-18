<?php

declare(strict_types=1);

namespace App\Services\Contracts;

use App\Models\HoraireBus;
use Illuminate\Support\Collection;

interface HoraireBusServiceInterface
{
    public function getAll(): Collection;

    public function getOne(int $id): HoraireBus;

    public function create(array $data): HoraireBus;

    public function update(HoraireBus $horaire, array $data): HoraireBus;

    public function delete(HoraireBus $horaire): void;
}
