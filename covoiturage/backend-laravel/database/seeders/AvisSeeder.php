<?php

namespace Database\Seeders;

use App\Models\Avis;
use App\Models\Trajet;
use App\Models\Membre;
use Illuminate\Database\Seeder;

class AvisSeeder extends Seeder
{
    public function run(): void
    {
        $trajetIds = Trajet::pluck('id')->toArray();
        $membreIds = Membre::pluck('id')->toArray();

        foreach ($trajetIds as $trajetId) {
            if (rand(0, 1)) {
                Avis::factory()->create([
                    'trajet_id' => $trajetId,
                    'reviewer_id' => $membreIds[array_rand($membreIds)],
                    'conducteur_id' => Trajet::find($trajetId)->membre_id,
                ]);
            }
        }
    }
}
