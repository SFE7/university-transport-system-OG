<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Membre;
use App\Models\Vehicule;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

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

    public function updateVehicule(Membre $conducteur, array $data, ?UploadedFile $photo = null): Vehicule
    {
        $payload = [
            'marque' => $data['marque'],
            'modele' => $data['modele'],
            'immatriculation' => $data['immatriculation'],
            'couleur' => $data['couleur'] ?? null,
        ];

        if ($photo) {
            $path = $photo->store('vehicules', 'public');
            $payload['photo_url'] = Storage::url($path);
        }

        return Vehicule::updateOrCreate(
            ['conducteur_id' => $conducteur->id],
            $payload
        );
    }
}
