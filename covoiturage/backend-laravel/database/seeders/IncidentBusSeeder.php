<?php

namespace Database\Seeders;

use App\Models\IncidentBus;
use App\Models\LigneBus;
use App\Models\Membre;
use Illuminate\Database\Seeder;

class IncidentBusSeeder extends Seeder
{
    public function run(): void
    {
        $lignes = LigneBus::pluck('id')->toArray();
        $membres = Membre::pluck('id')->toArray();

        foreach ($lignes as $ligne) {
            if (rand(0, 1)) {
                IncidentBus::factory()->create([
                    'ligne_bus_id' => $ligne,
                    'reported_by' => $membres[array_rand($membres)],
                ]);
            }
        }
    }
}
