<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfilRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = optional($this->user())->id;

        return [
            'name' => 'nullable|string|max:255',
            'phone' => 'nullable|string',
            'email' => [
                'nullable',
                'email',
                Rule::unique('membres', 'email')->ignore($userId),
            ],
        ];
    }
}
<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfilRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->user()?->id;

        return [
            'name' => 'nullable|string|max:255',
            'phone' => 'nullable|string',
            'email' => [
                'nullable',
                'email',
                Rule::unique('membres', 'email')->ignore($userId),
            ],
        ];
    }
}
