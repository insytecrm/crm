<?php

namespace App\Http\Requests\Platform;

use App\Support\Platform\PlanFormRules;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StorePlanWizardFeaturesRequest extends FormRequest
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
        return PlanFormRules::features();
    }

    protected function prepareForValidation(): void
    {
        $features = $this->input('features', []);

        if (is_array($features)) {
            $this->merge([
                'features' => collect($features)
                    ->map(fn (mixed $value): bool => filter_var($value, FILTER_VALIDATE_BOOLEAN))
                    ->all(),
            ]);
        }
    }
}
