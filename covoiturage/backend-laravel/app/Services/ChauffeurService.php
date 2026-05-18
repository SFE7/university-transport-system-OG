<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Membre;
use App\Services\Contracts\ChauffeurServiceInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;

class ChauffeurService implements ChauffeurServiceInterface
{
    public function getAll(): Collection
    {
        return Membre::query()
            ->where('role', 'chauffeur_bus')
            ->orderByDesc('created_at')
            ->get();
    }

    public function create(array $data): Membre
    {
        return Membre::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => 'chauffeur_bus',
        ]);
    }

    public function getOne(int $id): Membre
    {
        return Membre::findOrFail($id);
    }

    public function delete(Membre $chauffeur): void
    {
        abort_unless($chauffeur->role === 'chauffeur_bus', 404);

        $chauffeur->delete();
    }
}
