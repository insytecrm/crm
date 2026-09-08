<?php

namespace App\Http\Requests\Platform;

use App\Enums\BillingCycle;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreQuotationWizardPlanRequest extends FormRequest
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
            'plan_id' => ['required', 'integer', Rule::exists('plans', 'id')],
            'billing_cycle' => ['required', Rule::enum(BillingCycle::class)],
            'trial_enabled' => ['sometimes', 'boolean'],
            'trial_days' => ['nullable', 'integer', 'min:1', 'max:90', 'required_if:trial_enabled,1'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'trial_enabled' => $this->boolean('trial_enabled'),
        ]);
    }
}
