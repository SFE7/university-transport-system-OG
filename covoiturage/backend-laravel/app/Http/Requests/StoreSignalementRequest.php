<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSignalementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'conducteur_id' => [
                'required',
                'integer',
                Rule::exists('membres', 'id')->where('role', 'conducteur'),
                Rule::notIn([optional($this->user())->id]),
            ],
            'trajet_id' => ['nullable', 'integer', 'exists:trajets,id'],
            'raison' => 'required|string|min:10|max:1000',
            'description' => 'nullable|string|max:2000',
        ];
    }
}
