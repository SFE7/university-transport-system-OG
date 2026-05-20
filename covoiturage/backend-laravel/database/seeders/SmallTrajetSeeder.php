<?php

namespace Database\Seeders;

use App\Models\Trajet;
use App\Models\Membre;
use Illuminate\Database\Seeder;

class SmallTrajetSeeder extends Seeder
{
    public function run(): void
    {
        $membreIds = Membre::pluck('id')->toArray();

        // Create 3 trajets via factory and attach to random membre if available
        Trajet::factory()->count(3)->make()->each(function (Trajet $trajet) use ($membreIds): void {
            if (!empty($membreIds)) {
                $trajet->membre_id = $membreIds[array_rand($membreIds)];
            }
            $trajet->save();
        });
    }
}
