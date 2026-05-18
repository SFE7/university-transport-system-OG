<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ArretBus;
use App\Services\Contracts\ArretBusServiceInterface;

class ArretBusService implements ArretBusServiceInterface
{
    public function getOne(int $id): ArretBus
    {
        return ArretBus::findOrFail($id);
    }

    public function create(array $data): ArretBus
    {
        return ArretBus::create($data);
    }

    public function update(ArretBus $arret, array $data): ArretBus
    {
        $arret->update($data);

        return $arret;
    }

    public function delete(ArretBus $arret): void
    {
        $arret->delete();
    }
}
