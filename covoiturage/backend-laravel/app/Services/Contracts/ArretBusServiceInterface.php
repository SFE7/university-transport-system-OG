<?php

declare(strict_types=1);

namespace App\Services\Contracts;

use App\Models\ArretBus;

interface ArretBusServiceInterface
{
    public function getOne(int $id): ArretBus;

    public function create(array $data): ArretBus;

    public function update(ArretBus $arret, array $data): ArretBus;

    public function delete(ArretBus $arret): void;
}
