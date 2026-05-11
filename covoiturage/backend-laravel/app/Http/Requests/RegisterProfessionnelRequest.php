<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterProfessionnelRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:membres,email',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'nullable|string',
            'carte_identite' => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ];
    }
}
