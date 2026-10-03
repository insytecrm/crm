<?php

namespace App\Http\Requests\Tenant;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePropertyMicrositeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'microsite_enabled' => ['required', Rule::in(['0', '1', 0, 1, true, false])],
        ];
    }

    public function micrositeEnabled(): bool
    {
        return filter_var($this->validated('microsite_enabled'), FILTER_VALIDATE_BOOLEAN);
    }
}
