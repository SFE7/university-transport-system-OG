<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLigneBusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'color' => 'nullable|string|max:7',
            'arrets' => 'nullable|array',
            'arrets.*.name' => 'required_with:arrets|string|max:255',
            'arrets.*.latitude' => 'required_with:arrets|numeric',
            'arrets.*.longitude' => 'required_with:arrets|numeric',
            'arrets.*.order' => 'required_with:arrets|integer',
        ];
    }
}
