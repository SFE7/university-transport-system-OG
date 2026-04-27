<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTrajetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'departure_point' => 'required|string|max:255',
            'arrival_point' => 'required|string|max:255',
            'departure_time' => 'required|date|after:now',
            'available_seats' => 'required|integer|min:1',
        ];
    }
}
