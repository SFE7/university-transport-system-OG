<?php
declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLigneBusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $nameRule = $this->isMethod('POST')
            ? 'required|string|max:255'
            : 'sometimes|required|string|max:255';

        return [
            'name' => $nameRule,
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
