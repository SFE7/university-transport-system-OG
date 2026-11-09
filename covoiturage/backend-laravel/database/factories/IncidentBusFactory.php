<?php

namespace Database\Factories;

use App\Models\IncidentBus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<IncidentBus>
 */
class IncidentBusFactory extends Factory
{
    protected $model = IncidentBus::class;

    public function definition(): array
    {
        return [
            'ligne_bus_id' => null,
            'reported_by' => null,
            'type' => fake()->randomElement(['delay', 'breakdown', 'other']),
            'description' => fake()->sentence(),
            'resolved_at' => null,
        ];
    }
}
