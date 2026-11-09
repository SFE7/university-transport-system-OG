<?php
declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;

class StoreHoraireBusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'ligne_bus_id' => 'required|integer|exists:lignes_bus,id',
            'chauffeur_id' => 'required|integer|exists:membres,id',
            'departure_time' => 'required|date_format:H:i',
            'days' => 'required|array',
            'days.*' => 'in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $membre = \App\Models\Membre::find($this->chauffeur_id);
            if ($membre && $membre->role !== 'chauffeur_bus') {
                $validator->errors()->add('chauffeur_id', 'This membre is not a chauffeur de bus.');
            }
        });
    }
}
