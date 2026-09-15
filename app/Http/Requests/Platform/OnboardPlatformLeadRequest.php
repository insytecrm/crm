<?php

namespace App\Http\Requests\Platform;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class OnboardPlatformLeadRequest extends FormRequest
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
            'admin_name' => ['nullable', 'string', 'max:255'],
            'admin_email' => ['nullable', 'email', 'max:255'],
            'slug' => ['nullable', 'string', 'max:40', Rule::unique('tenants', 'id')],
            'rera_number' => ['nullable', 'string', 'max:100'],
            'gst_number' => ['nullable', 'string', 'max:30'],
            'email_credentials' => ['sometimes', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'email_credentials' => $this->boolean('email_credentials'),
        ]);
    }
}
