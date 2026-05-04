<?php

namespace Database\Factories;

use App\Models\BusPosition;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BusPosition>
 */
class BusPositionFactory extends Factory
{
    protected $model = BusPosition::class;

    public function definition(): array
    {
        return [
            'chauffeur_id' => null,
            'latitude' => fake()->latitude(-90, 90),
            'longitude' => fake()->longitude(-180, 180),
            'is_sharing' => fake()->boolean(70),
        ];
    }
}
