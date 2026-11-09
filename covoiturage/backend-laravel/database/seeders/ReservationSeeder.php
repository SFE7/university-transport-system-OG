<?php

namespace Database\Seeders;

use App\Models\Reservation;
use App\Models\Trajet;
use App\Models\Membre;
use Illuminate\Database\Seeder;

class ReservationSeeder extends Seeder
{
    public function run(): void
    {
        $trajetIds = Trajet::pluck('id')->toArray();
        $membreIds = Membre::pluck('id')->toArray();

        foreach ($trajetIds as $trajetId) {
            $count = rand(0, 3);
            for ($i = 0; $i < $count; $i++) {
                Reservation::factory()->create([
                    'trajet_id' => $trajetId,
                    'membre_id' => $membreIds[array_rand($membreIds)],
                    'status' => fake()->randomElement(['pending','accepted','cancelled']),
                ]);
            }
        }
    }
}
