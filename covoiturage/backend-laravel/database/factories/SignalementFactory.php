<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Signalement;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Signalement>
 */
class SignalementFactory extends Factory
{
    protected $model = Signalement::class;

    public function definition(): array
    {
        return [
            'reporter_id' => null,
            'reported_id' => null,
            'reason' => fake()->paragraph(),
            'status' => fake()->randomElement(['en_attente', 'traite', 'archive']),
        ];
    }
}
