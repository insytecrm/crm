<?php

namespace App\Http\Requests\Tenant;

use App\Enums\LeadBudget;
use App\Enums\LeadSource;
use App\Enums\PropertyType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLeadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    protected function prepareForValidation(): void
    {
        if ($this->input('source') === '') {
            $this->merge(['source' => null]);
        }

        $budget = LeadBudget::tryFromMixed($this->input('budget'));

        if ($budget instanceof LeadBudget) {
            $this->merge([
                'budget' => $budget->value,
            ]);
        }

        $propertyType = PropertyType::tryFromMixed($this->input('property_type'));

        if ($propertyType instanceof PropertyType) {
            $this->merge([
                'property_type' => $propertyType->value,
            ]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'source' => [
                'nullable',
                Rule::enum(LeadSource::class),
                Rule::in(array_map(
                    static fn (LeadSource $source): string => $source->value,
                    LeadSource::manualSelectableCases(),
                )),
            ],
            'budget' => ['nullable', Rule::enum(LeadBudget::class)],
            'location' => ['nullable', 'string', 'max:255'],
            'property_type' => ['nullable', Rule::enum(PropertyType::class)],
            'configuration' => ['nullable', 'string', 'max:255'],
            'assigned_to_id' => ['nullable', 'exists:users,id'],
            'lead_score' => ['nullable', 'integer', 'min:0', 'max:100'],
            'next_action' => ['nullable', 'string', 'max:255'],
        ];
    }
}
