<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\DocumentSoumis;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DocumentSoumis>
 */
class DocumentSoumisFactory extends Factory
{
    protected $model = DocumentSoumis::class;

    public function definition(): array
    {
        return [
            'membre_id' => null,
            'type' => fake()->randomElement(['carte_etudiante', 'carte_identite', 'permis_conduire', 'carte_grise']),
            'file_path' => 'documents/' . fake()->uuid() . '.pdf',
            'status' => fake()->randomElement(['en_attente', 'approuve', 'rejete']),
            'rejection_reason' => fake()->optional()->sentence(),
            'reviewed_by' => null,
            'reviewed_at' => fake()->optional()->dateTimeBetween('-1 month', 'now'),
        ];
    }
}
