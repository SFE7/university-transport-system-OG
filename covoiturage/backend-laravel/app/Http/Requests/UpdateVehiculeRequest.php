<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateVehiculeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $vehiculeId = $this->route('vehicule') ?? null;

        return [
            'marque' => 'required|string|max:100',
            'modele' => 'required|string|max:100',
            'immatriculation' => [
                'required',
                'string',
                'max:20',
                Rule::unique('vehicules', 'immatriculation')->ignore($vehiculeId),
            ],
            'couleur' => 'nullable|string|max:50',
            'photo' => 'nullable|image|max:2048',
        ];
    }
}
