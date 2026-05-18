<?php

declare(strict_types=1);

namespace App\Services\Contracts;

use App\Models\Membre;
use App\Models\Vehicule;

interface ProfilServiceInterface
{
    public function getProfile(int $membreId): ?Membre;

    public function updateProfile(Membre $membre, array $data): Membre;

    public function updateVehicule(Membre $conducteur, array $data): Vehicule;
}
