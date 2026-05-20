<?php

namespace Database\Factories;

use App\Models\HoraireBus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<HoraireBus>
 */
class HoraireBusFactory extends Factory
{
    protected $model = HoraireBus::class;

    public function definition(): array
    {
        return [
            'ligne_bus_id' => null,
            'chauffeur_id' => null,
            'departure_time' => fake()->time(),
            'days' => [1,2,3,4,5],
            'is_active' => true,
        ];
    }
}
