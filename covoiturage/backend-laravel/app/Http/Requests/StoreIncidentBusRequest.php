<?php
declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreIncidentBusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'ligne_bus_id' => 'required|integer|exists:lignes_bus,id',
            'type' => 'required|in:delay,breakdown,cancelled,other',
            'description' => 'required|string|max:1000',
        ];
    }
}
