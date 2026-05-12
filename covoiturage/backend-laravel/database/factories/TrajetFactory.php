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

    private const CAR_CATEGORIES = [
        'Sedan' => ['Peugeot 301', 'Hyundai Accent', 'Mitsubishi Attrage', 'Renault Symbol'],
        'City Hatchback' => ['Kia Picanto', 'Hyundai Grand i10', 'Suzuki Celerio', 'Renault Kwid'],
        'SUV' => ['Chery Tiggo', 'Hyundai Tucson', 'Kia Sportage', 'Dacia Duster'],
        'Compact Car' => ['Opel Astra', 'Peugeot 208', 'Citroën C3', 'Volkswagen Golf'],
        'Pickup' => ['Isuzu D-Max', 'Toyota Hilux', 'Ford Ranger'],
        'Family Van' => ['Fiat Doblo', 'Citroën Berlingo', 'Renault Kangoo'],
        'Luxury' => ['Mercedes C-Class', 'Audi a5', 'Lexus LC', 'BMW X1', 'BMW X2', 'Audi A3'],
    ];

    public function definition(): array
    {
        $category = array_rand(self::CAR_CATEGORIES);
        $model = self::CAR_CATEGORIES[$category][array_rand(self::CAR_CATEGORIES[$category])];

        return [
            'departure_point' => fake()->city(),
            'arrival_point' => fake()->city(),
            'departure_time' => fake()->dateTimeBetween('-1 week', '+2 weeks'),
            'available_seats' => fake()->numberBetween(1, 5),
            'car_category' => $category,
            'car_model' => $model,
            'status' => 'active',
            'membre_id' => null,
        ];
    }
}
