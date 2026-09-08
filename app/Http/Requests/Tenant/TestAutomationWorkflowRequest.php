<?php

namespace App\Http\Requests\Tenant;

use App\Enums\TenantPermission;
use App\Models\Lead;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TestAutomationWorkflowRequest extends FormRequest
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
        return [
            'lead_id' => ['required', 'integer', Rule::exists('leads', 'id')],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'lead_id.required' => __('Choose a lead to test this workflow.'),
        ];
    }

    public function lead(): Lead
    {
        return Lead::query()->findOrFail($this->integer('lead_id'));
    }
}
