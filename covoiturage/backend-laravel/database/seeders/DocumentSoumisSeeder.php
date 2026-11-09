<?php

namespace Database\Seeders;

use App\Models\DocumentSoumis;
use App\Models\Membre;
use Illuminate\Database\Seeder;

class DocumentSoumisSeeder extends Seeder
{
    public function run(): void
    {
        $membres = Membre::inRandomOrder()->take(30)->get();

        foreach ($membres as $membre) {
            DocumentSoumis::factory()->for($membre)->count(rand(0, 2))->create();
        }
    }
}
