<?php

namespace App\Http\Requests\Tenant;

use App\Enums\TenantPermission;
use App\Models\Automation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateAutomationWorkflowStatusRequest extends FormRequest
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
            'is_active' => ['required', Rule::in(['0', '1', 0, 1, true, false])],
        ];
    }

    /**
     * @return list<callable>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                /** @var Automation|null $workflow */
                $workflow = $this->route('workflow');

                if ($this->boolean('is_active') && $workflow?->trigger === null) {
                    $validator->errors()->add(
                        'is_active',
                        __('Choose a trigger before turning this workflow on.'),
                    );
                }

                if ($this->boolean('is_active') && $workflow?->trigger !== null && ! $workflow->trigger->isReady()) {
                    $validator->errors()->add(
                        'is_active',
                        __('That trigger is not available yet.'),
                    );
                }
            },
        ];
    }

    public function isActive(): bool
    {
        return filter_var($this->validated('is_active'), FILTER_VALIDATE_BOOLEAN);
    }
}
