<?php

namespace Database\Factories;

use App\Models\LigneBus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LigneBus>
 */
class LigneBusFactory extends Factory
{
    protected $model = LigneBus::class;

    public function definition(): array
    {
        return [
            'name' => 'Ligne ' . fake()->unique()->numberBetween(1, 100),
            'description' => fake()->sentence(),
            'is_active' => true,
        ];
    }
}
