<?php

namespace App\Http\Requests\Api;

use App\Enums\LeadBudget;
use App\Enums\LeadSource;
use App\Enums\PropertyType;
use App\Support\LeadSourcePath;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLeadApiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return tenancy()->initialized;
    }

    protected function prepareForValidation(): void
    {
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

        $providedSource = $this->input('source');
        $providedSubSource = $this->input('sub_source');
        $detail = null;

        if (filled($providedSubSource)) {
            $detail = (string) $providedSubSource;
        } elseif (filled($providedSource)) {
            $resolved = LeadSource::tryFromMixed($providedSource);

            if ($resolved === null || $resolved !== LeadSource::Api) {
                $detail = (string) $providedSource;
            }
        }

        $merge = [
            'source' => LeadSource::Api->value,
        ];

        if ($detail !== null && ! $this->filled('source_context')) {
            $path = LeadSourcePath::fromSegments([
                [
                    'key' => 'detail',
                    'id' => null,
                    'label' => $detail,
                ],
            ]);
            $merge['sub_source'] = $path['sub_source'];
            $merge['source_context'] = $path['source_context'];
        }

        $this->merge($merge);
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
            'source' => ['required', Rule::enum(LeadSource::class)],
            'sub_source' => ['nullable', 'string', 'max:500'],
            'source_context' => ['nullable', 'array'],
            'budget' => ['nullable', Rule::enum(LeadBudget::class)],
            'location' => ['nullable', 'string', 'max:255'],
            'property_type' => ['nullable', Rule::enum(PropertyType::class)],
            'configuration' => ['nullable', 'string', 'max:255'],
        ];
    }
}
