<?php

namespace App\Http\Requests\Tenant;

use App\Enums\PropertyType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateLeadPropertyTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    protected function prepareForValidation(): void
    {
        if ($this->input('property_type') === '') {
            $this->merge(['property_type' => null]);

            return;
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
            'property_type' => ['nullable', Rule::enum(PropertyType::class)],
        ];
    }
}
