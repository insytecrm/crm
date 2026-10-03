<?php

namespace App\Http\Requests\Platform;

use App\Support\Platform\PlanFormRules;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StorePlanWizardPricingRequest extends FormRequest
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
        return PlanFormRules::pricing();
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'trial_enabled' => $this->boolean('trial_enabled'),
        ]);
    }
}
