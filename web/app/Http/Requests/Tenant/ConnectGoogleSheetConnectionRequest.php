<?php

namespace App\Http\Requests\Tenant;

use App\Enums\GoogleSheetMappableField;
use App\Enums\TenantPermission;
use Illuminate\Foundation\Http\FormRequest;

class ConnectGoogleSheetConnectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission(TenantPermission::IntegrationsManage) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $rules = [];

        foreach (GoogleSheetMappableField::cases() as $field) {
            $rules["column_map.{$field->value}"] = [
                $field->isRequired() ? 'required' : 'nullable',
                'string',
                'max:255',
            ];
        }

        return $rules;
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'column_map.name.required' => __('Map the Name column before connecting.'),
        ];
    }

    /**
     * @return array<string, string|null>
     */
    public function columnMap(): array
    {
        /** @var array<string, string|null> $map */
        $map = $this->validated('column_map') ?? [];

        return $map;
    }
}
