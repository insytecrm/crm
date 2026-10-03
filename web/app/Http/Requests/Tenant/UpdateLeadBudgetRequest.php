<?php

namespace App\Http\Requests\Tenant;

use App\Enums\LeadBudget;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateLeadBudgetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    protected function prepareForValidation(): void
    {
        if ($this->input('budget') === '') {
            $this->merge(['budget' => null]);

            return;
        }

        $budget = LeadBudget::tryFromMixed($this->input('budget'));

        if ($budget instanceof LeadBudget) {
            $this->merge([
                'budget' => $budget->value,
            ]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'budget' => ['nullable', Rule::enum(LeadBudget::class)],
        ];
    }
}
