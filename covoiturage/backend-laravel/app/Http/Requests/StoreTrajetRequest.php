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
        $isStore = $this->isMethod('post');

        return [
            'departure_point' => 'required|string|max:255',
            'arrival_point' => 'required|string|max:255',
            'departure_time' => 'required|date|after:now',
            'available_seats' => 'required|integer|min:1',
            'car_category' => ($isStore ? 'required' : 'sometimes') . '|string|max:255',
            'car_model' => ($isStore ? 'required' : 'sometimes') . '|string|max:255',
            'car_photo_url' => 'nullable|string',
            'car_photo' => 'nullable|image:png|max:4096',
        ];
    }
}
