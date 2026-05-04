<?php

namespace Database\Factories;

use App\Models\Trajet;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Trajet>
 */
class TrajetFactory extends Factory
{
    protected $model = Trajet::class;

    public function definition(): array
    {
        return [
            'departure_point' => fake()->city(),
            'arrival_point' => fake()->city(),
            'departure_time' => fake()->dateTimeBetween('-1 week', '+2 weeks'),
            'available_seats' => fake()->numberBetween(1, 5),
            'status' => 'active',
            'membre_id' => null,
        ];
    }
}
