<?php

namespace App\Http\Requests\Platform;

use App\Enums\PlatformLeadSource;
use App\Enums\PlatformLeadStage;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePlatformLeadRequest extends FormRequest
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
            'contact_person' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'location' => ['nullable', 'string', 'max:255'],
            'source' => ['nullable', Rule::enum(PlatformLeadSource::class)],
            'owner_id' => ['nullable', 'integer', Rule::exists('users', 'id')->where('is_super_admin', true)],
            'stage' => ['nullable', Rule::enum(PlatformLeadStage::class)],
            '_add_lead_modal' => ['sometimes', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            '_add_lead_modal' => $this->boolean('_add_lead_modal'),
        ]);
    }
}
