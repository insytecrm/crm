<?php

namespace App\Http\Requests;

use App\Enums\TenantStatus;
use App\Models\Tenant;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class StoreTenantRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return (bool) $this->user()?->is_super_admin;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'string',
                'lowercase',
                'max:32',
                'regex:/^[a-z][a-z0-9]+$/',
                Rule::notIn(Tenant::ReservedIds),
                Rule::unique('tenants', 'id'),
            ],
            'email' => ['nullable', 'string', 'lowercase', 'email', 'max:255'],
            'status' => ['required', Rule::enum(TenantStatus::class)],
            'admin_name' => ['required', 'string', 'max:255'],
            'admin_email' => ['required', 'string', 'lowercase', 'email', 'max:255'],
            'admin_password' => ['required', 'confirmed', Password::defaults()],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'slug.regex' => 'The slug must start with a letter and contain only lowercase letters and numbers.',
            'slug.not_in' => 'This slug is reserved.',
        ];
    }
}
