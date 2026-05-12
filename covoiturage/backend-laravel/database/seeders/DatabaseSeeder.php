<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // basic user for local testing
        User::firstOrCreate([
            'email' => 'test@example.com',
        ], [
            'name' => 'Test User',
            'email_verified_at' => now(),
            'password' => bcrypt('password'),
        ]);

        // run application seeders
        $this->call([
            MembreSeeder::class,
            VehiculeSeeder::class,
            DocumentSoumisSeeder::class,
            SignalementSeeder::class,
            TrajetSeeder::class,
            ReservationSeeder::class,
            AvisSeeder::class,
            NotificationSeeder::class,
        ]);
    }
}
