<?php

namespace App\Http\Requests\Platform;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StartPlatformLeadTrialRequest extends FormRequest
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
            'company_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'plan_id' => ['required', 'integer', Rule::exists('plans', 'id')],
            'trial_days' => ['required', 'integer', 'min:1', 'max:90'],
            'rera_number' => ['nullable', 'string', 'max:100'],
            'gst_number' => ['nullable', 'string', 'max:30'],
            'slug' => ['nullable', 'string', 'max:40', Rule::unique('tenants', 'id')],
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
