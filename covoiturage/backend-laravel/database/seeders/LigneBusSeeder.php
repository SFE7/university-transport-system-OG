<?php

namespace Database\Seeders;

use App\Models\LigneBus;
use App\Models\ArretBus;
use Illuminate\Database\Seeder;

class LigneBusSeeder extends Seeder
{
    public function run(): void
    {
        LigneBus::factory()->count(5)->create()->each(function ($ligne) {
            // create 5 stops per line
            for ($i = 1; $i <= 5; $i++) {
                ArretBus::factory()->create([
                    'ligne_bus_id' => $ligne->id,
                    'order' => $i,
                ]);
            }
        });
    }
}
