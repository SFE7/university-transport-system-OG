<?php

namespace Database\Seeders;

use App\Models\Membre;
use App\Models\Vehicule;
use Illuminate\Database\Seeder;

class VehiculeSeeder extends Seeder
{
    public function run(): void
    {
        // ensure we have some conducteurs
        $conducteurs = Membre::where('role', 'conducteur')->get();

        if ($conducteurs->isEmpty()) {
            $conducteurs = Membre::factory()->count(5)->state(['role' => 'conducteur'])->create();
        }

        foreach ($conducteurs as $conducteur) {
            Vehicule::factory()->for($conducteur, 'conducteur')->create();
        }
    }
}
