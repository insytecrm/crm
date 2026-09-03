<?php

namespace App\Http\Requests\Tenant;

use App\Enums\LeadStatus;
use App\Enums\TenantPermission;
use Illuminate\Validation\Rule;

class BulkUpdateLeadStatusRequest extends BulkLeadsRequest
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
            'status' => ['required', Rule::enum(LeadStatus::class)->except(LeadStatus::Converted)],
        ];
    }
}
