<?php

namespace Database\Seeders;

use App\Models\BusPosition;
use App\Models\Membre;
use Illuminate\Database\Seeder;

class BusPositionSeeder extends Seeder
{
    public function run(): void
    {
        $drivers = Membre::where('role', 'chauffeur_bus')->pluck('id')->toArray();

        foreach ($drivers as $driverId) {
            BusPosition::factory()->create([
                'chauffeur_id' => $driverId,
            ]);
        }
    }
}
