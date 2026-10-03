<?php

namespace App\Http\Requests\Tenant;

use App\Enums\TenantPermission;

class BulkAssignLeadsRequest extends BulkLeadsRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission(TenantPermission::LeadsUpdate) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            ...parent::rules(),
            'assigned_to_id' => ['nullable', 'exists:users,id'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->input('assigned_to_id') === '') {
            $this->merge(['assigned_to_id' => null]);
        }
    }
}
