<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Vehicule;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Vehicule>
 */
class VehiculeFactory extends Factory
{
    protected $model = Vehicule::class;

    public function definition(): array
    {
        return [
            'conducteur_id' => null,
            'marque' => fake()->company(),
            'modele' => fake()->word(),
            'immatriculation' => strtoupper(fake()->bothify('###-???')),
            'couleur' => fake()->optional()->safeColorName(),
        ];
    }
}
