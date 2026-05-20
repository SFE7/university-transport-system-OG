<?php

namespace Database\Seeders;

use App\Models\Trajet;
use App\Models\Membre;
use Illuminate\Database\Seeder;

class TrajetSeeder extends Seeder
{
    private const CAR_CATEGORIES = [
        'Sedan' => ['Peugeot 301', 'Hyundai Accent', 'Mitsubishi Attrage', 'Renault Symbol'],
        'City Hatchback' => ['Kia Picanto', 'Hyundai Grand i10', 'Suzuki Celerio', 'Renault Kwid'],
        'SUV' => ['Chery Tiggo', 'Hyundai Tucson', 'Kia Sportage', 'Dacia Duster'],
        'Compact Car' => ['Opel Astra', 'Peugeot 208', 'Citroën C3', 'Volkswagen Golf'],
        'Pickup' => ['Isuzu D-Max', 'Toyota Hilux', 'Ford Ranger'],
        'Family Van' => ['Fiat Doblo', 'Citroën Berlingo', 'Renault Kangoo'],
        'Luxury' => ['Mercedes C-Class', 'Audi a5', 'Lexus LC', 'BMW X1', 'BMW X2', 'Audi A3'],
    ];

    public function run(): void
    {
        $membreIds = Membre::pluck('id')->toArray();

        Trajet::query()->where(function ($query): void {
            $query->whereNull('car_category')->orWhereNull('car_model');
        })->get()->each(function (Trajet $trajet) use ($membreIds): void {
            $category = array_rand(self::CAR_CATEGORIES);
            $model = self::CAR_CATEGORIES[$category][array_rand(self::CAR_CATEGORIES[$category])];

            $trajet->car_category = $category;
            $trajet->car_model = $model;

            if (empty($trajet->membre_id) && !empty($membreIds)) {
                $trajet->membre_id = $membreIds[array_rand($membreIds)];
            }

            $trajet->save();
        });

        if (Trajet::query()->count() === 0 && !empty($membreIds)) {
            Trajet::factory()->count(30)->make()->each(function (Trajet $trajet) use ($membreIds): void {
                $trajet->membre_id = $membreIds[array_rand($membreIds)];
                $trajet->save();
            });
        }

        $categories = self::CAR_CATEGORIES;
        Trajet::where('car_category', 'Luxury')->get()->each(function (Trajet $trajet) use ($categories): void {
            $model = $categories['Luxury'][array_rand($categories['Luxury'])];
            $trajet->car_model = $model;
            $trajet->save();
        });
    }
}
