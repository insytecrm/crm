<?php

namespace App\Http\Requests\Tenant;

use App\Enums\FacebookLeadMappableField;
use App\Enums\TenantPermission;
use Illuminate\Foundation\Http\FormRequest;

class ActivateFacebookPageConnectionRequest extends FormRequest
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
        $rules = [
            'form_ids' => ['required', 'array', 'min:1'],
            'form_ids.*' => ['required', 'string', 'max:64'],
            'field_map' => ['required', 'array'],
        ];

        foreach (FacebookLeadMappableField::cases() as $field) {
            $rules['field_map.'.$field->value] = [
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
            'form_ids.required' => __('Select at least one Lead Form.'),
            'field_map.name.required' => __('Map the Name field before activating.'),
        ];
    }

    /**
     * @return list<string>
     */
    public function formIds(): array
    {
        return array_values($this->validated('form_ids'));
    }

    /**
     * @return array<string, string|null>
     */
    public function fieldMap(): array
    {
        return $this->validated('field_map');
    }
}
