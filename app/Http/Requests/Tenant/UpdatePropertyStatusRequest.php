<?php

namespace App\Http\Requests\Tenant;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePropertyStatusRequest extends FormRequest
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
            'is_active' => ['required', Rule::in(['0', '1', 0, 1, true, false])],
        ];
    }

    public function isActive(): bool
    {
        return filter_var($this->validated('is_active'), FILTER_VALIDATE_BOOLEAN);
    }
}
