<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class UpdateHoraireBusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'ligne_bus_id' => 'sometimes|required|integer|exists:lignes_bus,id',
            'chauffeur_id' => 'sometimes|required|integer|exists:membres,id',
            'departure_time' => 'sometimes|required|date_format:H:i',
            'days' => 'sometimes|required|array',
            'days.*' => 'in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if (! $this->filled('chauffeur_id')) {
                return;
            }

            $membre = \App\Models\Membre::find($this->chauffeur_id);
            if ($membre && $membre->role !== 'chauffeur_bus') {
                $validator->errors()->add('chauffeur_id', 'This membre is not a chauffeur de bus.');
            }
        });
    }
}
