<?php

namespace App\Http\Requests\Tenant;

use App\Enums\TenantPermission;
use Illuminate\Foundation\Http\FormRequest;

class StoreAutomationWorkflowRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission(TenantPermission::AutomationsManage) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [];
    }
}
