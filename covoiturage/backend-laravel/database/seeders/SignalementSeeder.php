<?php

namespace Database\Seeders;

use App\Models\Membre;
use App\Models\Signalement;
use Illuminate\Database\Seeder;

class SignalementSeeder extends Seeder
{
    public function run(): void
    {
        $membres = Membre::all();
        if ($membres->count() < 2) {
            return;
        }

        // create a number of random reports between existing members
        for ($i = 0; $i < 30; $i++) {
            $reporter = $membres->random();
            $reported = $membres->where('id', '!=', $reporter->id)->random();

            Signalement::factory()->create([
                'reporter_id' => $reporter->id,
                'reported_id' => $reported->id,
            ]);
        }
    }
}
