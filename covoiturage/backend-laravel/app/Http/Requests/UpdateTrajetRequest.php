<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTrajetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'departure_point' => 'sometimes|required|string|max:255',
            'arrival_point' => 'sometimes|required|string|max:255',
            'departure_time' => 'sometimes|required|date|after:now',
            'available_seats' => 'sometimes|required|integer|min:1',
            'car_category' => 'sometimes|string|max:255',
            'car_model' => 'sometimes|string|max:255',
            'car_photo_url' => 'nullable|string',
            'car_photo' => 'nullable|image:png|max:4096',
        ];
    }
}
