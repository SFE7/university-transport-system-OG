<?php

namespace Database\Seeders;

use App\Models\Trajet;
use App\Models\Membre;
use Illuminate\Database\Seeder;

class TrajetSeeder extends Seeder
{
    public function run(): void
    {
        $membreIds = Membre::pluck('id')->toArray();

        // create 30 trajets assigned to random membres
        Trajet::factory()->count(30)->make()->each(function ($trajet) use ($membreIds) {
            $trajet->membre_id = $membreIds[array_rand($membreIds)];
            $trajet->save();
        });
    }
}
