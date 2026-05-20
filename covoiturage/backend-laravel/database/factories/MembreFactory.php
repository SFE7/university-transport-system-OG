<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Membre;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends Factory<Membre>
 */
class MembreFactory extends Factory
{
    protected $model = Membre::class;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'password' => Hash::make('password'),
            'role' => 'membre',
            'phone' => fake()->phoneNumber(),
            'is_banned' => false,
            'account_type' => fake()->randomElement(['etudiant', 'professionnel']),
            'carte_etudiante_path' => null,
            'has_verified_documents' => false,
        ];
    }
}
