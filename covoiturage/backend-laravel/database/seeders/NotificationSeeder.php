<?php

namespace Database\Seeders;

use App\Models\Notification;
use App\Models\Membre;
use Illuminate\Database\Seeder;

class NotificationSeeder extends Seeder
{
    public function run(): void
    {
        $membres = Membre::pluck('id')->toArray();

        foreach ($membres as $membre) {
            if (rand(0, 3) === 0) {
                Notification::factory()->create([
                    'membre_id' => $membre,
                ]);
            }
        }
    }
}
