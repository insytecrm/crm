<?php

namespace App\Http\Requests\Tenant;

use App\Enums\AutomationActionType;
use App\Enums\AutomationConditionField;
use App\Enums\AutomationConditionOperator;
use App\Enums\AutomationTrigger;
use App\Enums\LeadStatus;
use App\Enums\TenantPermission;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateAutomationWorkflowRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'is_active' => ['sometimes', 'boolean'],
            'trigger' => ['nullable', Rule::enum(AutomationTrigger::class)],
            'conditions' => ['sometimes', 'array', 'max:20'],
            'conditions.*.field' => ['required', Rule::enum(AutomationConditionField::class)],
            'conditions.*.operator' => ['required', Rule::enum(AutomationConditionOperator::class)],
            'conditions.*.value' => ['nullable', 'string', 'max:255'],
            'actions' => ['sometimes', 'array', 'max:10'],
            'actions.*.type' => ['required', Rule::enum(AutomationActionType::class)],
            'actions.*.title' => ['sometimes', 'nullable', 'string', 'max:255'],
            'actions.*.body' => ['sometimes', 'nullable', 'string', 'max:5000'],
            'actions.*.status' => ['sometimes', 'nullable', Rule::enum(LeadStatus::class)->except([LeadStatus::Converted, LeadStatus::Lost])],
            'actions.*.delay_hours' => ['sometimes', 'nullable', 'integer', 'min:1', 'max:8760'],
            'actions.*.delay_minutes' => ['sometimes', 'nullable', 'integer', 'min:1', 'max:10080'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => __('Please name this workflow.'),
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

                if ($this->boolean('is_active') && blank($this->input('trigger'))) {
                    $validator->errors()->add(
                        'is_active',
                        __('Choose a trigger before turning this workflow on.'),
                    );
                }

                $trigger = AutomationTrigger::tryFrom((string) $this->input('trigger'));

                if ($this->boolean('is_active') && $trigger !== null && ! $trigger->isReady()) {
                    $validator->errors()->add(
                        'trigger',
                        __('That trigger is not available yet.'),
                    );
                }
            },
        ];
    }

    /**
     * @return array{
     *     name: string,
     *     is_active: bool,
     *     trigger: ?string,
     *     conditions: list<array{field: string, operator: string, value?: ?string}>,
     *     actions: list<array{type: string, title?: ?string, body?: ?string, status?: ?string, delay_hours?: int|null}>
     * }
     */
    public function workflowData(): array
    {
        $validated = $this->validated();

        return [
            'name' => $validated['name'],
            'is_active' => $this->boolean('is_active'),
            'trigger' => $validated['trigger'] ?? null,
            'conditions' => array_values($validated['conditions'] ?? []),
            'actions' => array_values($validated['actions'] ?? []),
        ];
    }
}
