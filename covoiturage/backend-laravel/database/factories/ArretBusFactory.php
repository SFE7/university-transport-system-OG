<?php

namespace Database\Factories;

use App\Models\ArretBus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ArretBus>
 */
class ArretBusFactory extends Factory
{
    protected $model = ArretBus::class;

    public function definition(): array
    {
        return [
            'name' => fake()->streetName(),
            'latitude' => fake()->latitude(-90, 90),
            'longitude' => fake()->longitude(-180, 180),
            'order' => 0,
            'ligne_bus_id' => null,
        ];
    }
}
