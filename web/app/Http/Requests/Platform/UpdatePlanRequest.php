<?php

namespace App\Http\Requests\Platform;

use App\Support\Platform\PlanFormRules;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePlanRequest extends FormRequest
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
            ...PlanFormRules::basic(),
            ...PlanFormRules::pricing(),
            ...PlanFormRules::features(),
            ...PlanFormRules::limits(),
        ];
    }

    protected function prepareForValidation(): void
    {
        $features = $this->input('features', []);

        $this->merge([
            'trial_enabled' => $this->boolean('trial_enabled'),
            'features' => is_array($features)
                ? collect($features)->map(fn (mixed $value): bool => filter_var($value, FILTER_VALIDATE_BOOLEAN))->all()
                : [],
        ]);
    }
}
