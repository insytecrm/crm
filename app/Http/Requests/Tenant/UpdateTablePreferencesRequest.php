<?php

namespace App\Http\Requests\Tenant;

use App\Support\DataTable\DataTableRegistry;
use App\Support\DataTable\TablePreferencesSupport;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTablePreferencesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $tableKey = (string) $this->route('tableKey');
        $definition = DataTableRegistry::get($tableKey);
        $defaults = TablePreferencesSupport::defaults($definition);

        $columnRules = [];

        foreach (array_keys($defaults['columns']) as $column) {
            $columnRules["columns.{$column}"] = ['sometimes', 'boolean'];
        }

        $listingRules = [];

        if (TablePreferencesSupport::usesListings($tableKey)) {
            $listingRules['listing'] = [
                'required',
                'string',
                Rule::in(TablePreferencesSupport::listingKeys($tableKey)),
            ];
        }

        return array_merge($listingRules, $columnRules, [
            'columns' => ['sometimes', 'array'],
            'custom_columns' => ['sometimes', 'array'],
            'custom_columns.*.key' => ['sometimes', 'string', 'max:100'],
            'custom_columns.*.label' => ['required_with:custom_columns', 'string', 'max:100'],
            'custom_columns.*.type' => ['sometimes', Rule::in(['text', 'select'])],
            'custom_columns.*.options' => ['sometimes', 'array'],
            'custom_columns.*.options.*' => ['string', 'max:100'],
            'custom_columns.*.visible' => ['sometimes', 'boolean'],
        ]);
    }

    public function listingKey(): ?string
    {
        $tableKey = (string) $this->route('tableKey');

        if (! TablePreferencesSupport::usesListings($tableKey)) {
            return null;
        }

        return $this->string('listing')->toString();
    }
}
