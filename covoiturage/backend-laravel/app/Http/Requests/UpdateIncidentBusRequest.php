<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateIncidentBusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'ligne_bus_id' => 'sometimes|required|integer|exists:lignes_bus,id',
            'type' => 'sometimes|required|in:delay,breakdown,cancelled,other',
            'description' => 'sometimes|required|string|max:1000',
        ];
    }
}
