<?php

namespace Database\Factories;

use App\Models\Notification;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Notification>
 */
class NotificationFactory extends Factory
{
    protected $model = Notification::class;

    public function definition(): array
    {
        return [
            'membre_id' => null,
            'message' => fake()->sentence(),
            'type' => fake()->randomElement(['info', 'warning', 'alert']),
            'is_read' => false,
        ];
    }
}
