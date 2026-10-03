<?php

namespace App\Http\Requests;

use App\Contracts\PlatformPlanCatalog;
use App\Models\Tenant;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreChannelPartnerWizardRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->is_super_admin;
    }

    protected function getRedirectUrl(): string
    {
        return route('tenants.index', ['add' => 1]);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $planKeys = collect(app(PlatformPlanCatalog::class)->options())->pluck('key')->all();

        return [
            'name' => ['required', 'string', 'max:255'],
            'owner_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:40'],
            'location' => ['nullable', 'string', 'max:255'],
            'slug' => [
                'nullable',
                'string',
                'lowercase',
                'max:32',
                'regex:/^[a-z][a-z0-9]+$/',
                Rule::notIn(Tenant::ReservedIds),
                Rule::unique('tenants', 'id'),
            ],
            'plan_key' => ['required', Rule::in($planKeys)],
            'billing_cycle' => ['required', Rule::in(['monthly', 'annual'])],
            'start_trial' => ['nullable', 'boolean'],
            'trial_days' => ['nullable', 'integer', 'min:1', 'max:90'],
            'admin_name' => ['required', 'string', 'max:255'],
            'admin_email' => ['required', 'string', 'lowercase', 'email', 'max:255'],
            'send_invitation' => ['nullable', 'boolean'],
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
