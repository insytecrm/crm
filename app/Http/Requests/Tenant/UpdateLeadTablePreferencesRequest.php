<?php

namespace App\Http\Requests\Tenant;

use App\Enums\LeadListingFilter;
use App\Support\LeadTablePreferences;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateLeadTablePreferencesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function listing(): LeadListingFilter
    {
        return LeadListingFilter::fromRequest($this->string('listing')->toString());
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $defaults = LeadTablePreferences::defaults();

        return [
            'listing' => ['required', 'string', Rule::in(LeadTablePreferences::listingKeys())],
            'columns' => ['required', 'array'],
            'columns.name' => ['required', 'boolean', Rule::in([true])],
            'actions' => ['required', 'array'],
            ...collect($defaults['columns'])
                ->reject(fn (bool $value, string $key) => $key === 'name')
                ->mapWithKeys(fn (bool $value, string $key) => ["columns.{$key}" => ['required', 'boolean']])
                ->all(),
            ...collect($defaults['actions'])
                ->mapWithKeys(fn (bool $value, string $key) => ["actions.{$key}" => ['required', 'boolean']])
                ->all(),
        ];
    }
}
