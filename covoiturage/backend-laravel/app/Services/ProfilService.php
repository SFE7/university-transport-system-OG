<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Membre;
use App\Models\Vehicule;

class ProfilService
{
    public function getProfile(int $membreId): ?Membre
    {
        return Membre::with('vehicule')->find($membreId);
    }

    public function updateProfile(Membre $membre, array $data): Membre
    {
        $membre->fill(array_filter($data, fn($v) => $v !== null));
        $membre->save();
        return $membre;
    }

    public function updateVehicule(Membre $conducteur, array $data): Vehicule
    {
        return Vehicule::updateOrCreate(
            ['conducteur_id' => $conducteur->id],
            [
                'marque' => $data['marque'],
                'modele' => $data['modele'],
                'immatriculation' => $data['immatriculation'],
                'couleur' => $data['couleur'] ?? null,
            ]
        );
    }
}
