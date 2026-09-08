<?php

namespace App\Http\Requests\Platform;

use App\Models\Tenant;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class OnboardQuotationRequest extends FormRequest
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
            'admin_name' => ['required', 'string', 'max:255'],
            'admin_email' => ['required', 'email', 'max:255'],
            'slug' => ['nullable', 'string', 'max:32', 'alpha_dash:ascii', Rule::notIn(Tenant::ReservedIds), Rule::unique('tenants', 'id')],
        ];
    }
}
