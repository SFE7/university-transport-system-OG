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
            'reported_id' => ['required', 'exists:membres,id', Rule::notIn([optional($this->user())->id])],
            'reason' => 'required|string|min:10|max:1000',
        ];
    }
}
