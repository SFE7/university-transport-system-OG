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
<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreSignalementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'reported_id' => 'required|exists:membres,id|different:reporter_id',
            'reason' => 'required|string|min:10|max:1000',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($this->user() && (int) $this->user()->id === (int) $this->input('reported_id')) {
                $validator->errors()->add('reported_id', 'You cannot report yourself.');
            }
        });
    }
}
