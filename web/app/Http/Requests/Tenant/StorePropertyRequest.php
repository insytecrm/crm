<?php

namespace App\Http\Requests\Tenant;

use App\Enums\ProjectStatus;
use App\Enums\PropertyType;
use App\Http\Requests\Tenant\Concerns\NormalizesPropertyAmenities;
use App\Http\Requests\Tenant\Concerns\NormalizesPropertyConfigurations;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePropertyRequest extends FormRequest
{
    use NormalizesPropertyAmenities;
    use NormalizesPropertyConfigurations;

    public function authorize(): bool
    {
        return auth()->check();
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'property_type' => PropertyType::tryFromMixed($this->input('property_type'))?->value,
            'project_status' => ProjectStatus::tryFromMixed($this->input('project_status'))?->value,
            'amenities' => $this->normalizeAmenities($this->input('amenities')),
            'configurations' => $this->normalizeConfigurations($this->input('configurations')),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return array_merge(
            $this->basicInformationRules(),
            $this->projectScaleRules(),
            $this->taggingAndPayoutRules(),
            $this->amenitiesRules(),
            $this->configurationsRules(),
            $this->attachmentsRules(),
            $this->micrositeRules(),
        );
    }

    /**
     * @return array<string, mixed>
     */
    protected function micrositeRules(): array
    {
        return [
            'microsite_enabled' => ['sometimes', Rule::in(['0', '1', 0, 1, true, false])],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function attachmentsRules(): array
    {
        return [
            'layout_files' => ['nullable', 'array', 'max:20'],
            'layout_files.*' => ['file', 'max:10240', 'mimes:pdf,jpg,jpeg,png,webp'],
            'brochure_files' => ['nullable', 'array', 'max:20'],
            'brochure_files.*' => ['file', 'max:10240', 'mimes:pdf,jpg,jpeg,png,webp'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function basicInformationRules(): array
    {
        return [
            'developer_name' => ['nullable', 'string', 'max:255'],
            'project_name' => ['required', 'string', 'max:255'],
            'project_location' => ['nullable', 'string', 'max:255'],
            'rera_number' => ['nullable', 'string', 'max:50', 'regex:/^[A-Za-z0-9\-\/]+$/'],
            'property_type' => ['nullable', Rule::enum(PropertyType::class)],
            'project_status' => ['nullable', Rule::enum(ProjectStatus::class)],
            'possession_date' => ['nullable', 'string', 'regex:/^(0[1-9]|1[0-2])-\d{4}$/'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function projectScaleRules(): array
    {
        return [
            'total_land_parcel_acres' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
            'total_towers' => ['nullable', 'integer', 'min:0', 'max:65535'],
            'total_floors' => ['nullable', 'string', 'max:50', 'regex:/^[A-Za-z0-9+\-]+$/'],
            'carpet_area_from_sqft' => ['nullable', 'integer', 'min:0'],
            'carpet_area_to_sqft' => ['nullable', 'integer', 'min:0', 'gte:carpet_area_from_sqft'],
            'price_from' => ['nullable', 'integer', 'min:0'],
            'price_to' => ['nullable', 'integer', 'min:0', 'gte:price_from'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function taggingAndPayoutRules(): array
    {
        return [
            'tagging_period_days' => ['nullable', 'integer', 'min:1', 'max:65535'],
            'payout_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'sourcing_manager_name' => ['nullable', 'string', 'max:255'],
            'sourcing_manager_contact' => ['nullable', 'string', 'max:30'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function amenitiesRules(): array
    {
        return [
            'amenities' => ['nullable', 'array', 'max:50'],
            'amenities.*' => ['required', 'string', 'max:100', 'distinct:ignore_case'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function configurationsRules(): array
    {
        return [
            'configurations' => ['nullable', 'array', 'max:50'],
            'configurations.*.name' => ['required', 'string', 'max:100'],
            'configurations.*.carpet_area_sqft' => ['nullable', 'integer', 'min:0'],
            'configurations.*.price' => ['nullable', 'integer', 'min:0'],
            'configurations.*.unit_count' => ['nullable', 'integer', 'min:0'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'possession_date.regex' => __('Possession date must be in MM-YYYY format.'),
            'rera_number.regex' => __('RERA number may only contain letters, numbers, slashes, and hyphens.'),
            'total_floors.regex' => __('Total floors may only contain letters, numbers, and + or - symbols.'),
            'carpet_area_to_sqft.gte' => __('Carpet area "To" must be greater than or equal to "From".'),
            'price_to.gte' => __('Price "To" must be greater than or equal to "From".'),
        ];
    }
}
