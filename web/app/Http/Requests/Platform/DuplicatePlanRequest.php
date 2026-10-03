<?php

namespace App\Http\Requests\Platform;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class DuplicatePlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->is_super_admin;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'copy_pricing' => ['sometimes', 'boolean'],
            'copy_features' => ['sometimes', 'boolean'],
            'copy_limits' => ['sometimes', 'boolean'],
            'copy_trial' => ['sometimes', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'copy_pricing' => $this->boolean('copy_pricing'),
            'copy_features' => $this->boolean('copy_features'),
            'copy_limits' => $this->boolean('copy_limits'),
            'copy_trial' => $this->boolean('copy_trial'),
        ]);
    }
}
