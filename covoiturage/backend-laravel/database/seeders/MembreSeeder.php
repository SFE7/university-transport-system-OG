<?php

namespace Database\Seeders;

use App\Models\Membre;
use Illuminate\Database\Seeder;

class MembreSeeder extends Seeder
{
    public function run(): void
    {
        // create regular members
        Membre::factory()->count(20)->create();

        // create some drivers
        Membre::factory()->count(5)->state([ 'role' => 'chauffeur_bus' ])->create();

        // a known admin/test member
        Membre::factory()->create([
            'name' => 'Seed Admin',
            'email' => 'admin@example.com',
            'role' => 'admin',
        ]);
    }
}
