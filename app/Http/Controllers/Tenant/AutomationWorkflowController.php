<?php

namespace App\Http\Controllers\Tenant;

use App\Actions\CreateAutomationWorkflow;
use App\Actions\RunAutomation;
use App\Actions\SaveAutomationWorkflow;
use App\Enums\AutomationActionType;
use App\Enums\AutomationConditionField;
use App\Enums\AutomationConditionOperator;
use App\Enums\AutomationTrigger;
use App\Enums\AutomationWorkflowFilter;
use App\Enums\LeadBudget;
use App\Enums\LeadSource;
use App\Enums\LeadStatus;
use App\Enums\PlanCapability;
use App\Enums\PropertyType;
use App\Enums\SiteVisitOutcome;
use App\Enums\TenantPermission;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\StoreAutomationWorkflowRequest;
use App\Http\Requests\Tenant\TestAutomationWorkflowRequest;
use App\Http\Requests\Tenant\UpdateAutomationWorkflowRequest;
use App\Http\Requests\Tenant\UpdateAutomationWorkflowStatusRequest;
use App\Models\Automation;
use App\Models\Lead;
use App\Models\Property;
use App\Support\Platform\TenantPlanAccess;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AutomationWorkflowController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()?->hasPermission(TenantPermission::AutomationsView), 403);

        $filter = AutomationWorkflowFilter::fromRequest($request->string('filter')->toString());
        $search = $request->string('search')->trim()->toString();

        $workflows = Automation::query()
            ->ownedBy($request->user())
            ->with(['actions'])
            ->when($filter === AutomationWorkflowFilter::On, fn ($query) => $query->where('is_active', true))
            ->when($filter === AutomationWorkflowFilter::Off, fn ($query) => $query->where('is_active', false))
            ->when($search !== '', function ($query) use ($search): void {
                $query->where('name', 'like', '%'.$search.'%');
            })
            ->latest('created_at')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('tenant.automations.workflows', [
            'workflows' => $workflows,
            'filter' => $filter,
            'filters' => AutomationWorkflowFilter::cases(),
            'search' => $search,
            'canManage' => $request->user()?->hasPermission(TenantPermission::AutomationsManage) ?? false,
        ]);
    }

    public function store(StoreAutomationWorkflowRequest $request, CreateAutomationWorkflow $createAutomationWorkflow): RedirectResponse
    {
        $workflow = $createAutomationWorkflow->handle($request->user());

        return redirect()
            ->route('tenant.automations.workflows.edit', $workflow)
            ->with('status', __('Workflow created.'));
    }

    public function edit(Request $request, Automation $workflow): View
    {
        abort_unless($request->user()?->hasPermission(TenantPermission::AutomationsManage), 403);

        $workflow->load(['conditions', 'actions']);

        return view('tenant.automations.edit', [
            'workflow' => $workflow,
            'editorConfig' => $this->editorConfig($workflow),
        ]);
    }

    public function update(
        UpdateAutomationWorkflowRequest $request,
        Automation $workflow,
        SaveAutomationWorkflow $saveAutomationWorkflow,
    ): RedirectResponse {
        $saveAutomationWorkflow->handle($workflow, $request->workflowData());

        return redirect()
            ->route('tenant.automations.workflows.edit', $workflow)
            ->with('status', __('Workflow saved.'));
    }

    public function updateStatus(
        UpdateAutomationWorkflowStatusRequest $request,
        Automation $workflow,
    ): RedirectResponse {
        $workflow->update([
            'is_active' => $request->isActive(),
        ]);

        return back()->with('status', $request->isActive()
            ? __('Workflow turned on.')
            : __('Workflow turned off.'));
    }

    public function destroy(Request $request, Automation $workflow): RedirectResponse
    {
        abort_unless($request->user()?->hasPermission(TenantPermission::AutomationsManage), 403);

        $workflow->delete();

        return redirect()
            ->route('tenant.automations.workflows')
            ->with('status', __('Workflow deleted.'));
    }

    public function test(
        TestAutomationWorkflowRequest $request,
        Automation $workflow,
        RunAutomation $runAutomation,
    ): RedirectResponse {
        $run = $runAutomation->handle($workflow, [
            'lead_id' => $request->lead()->id,
        ], true);

        return back()
            ->with('status', $run->message())
            ->with('automation_test_result', [
                'status' => $run->status->value,
                'message' => $run->message(),
                'checks' => $run->result['checks'] ?? [],
                'steps' => $run->result['steps'] ?? [],
            ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function editorConfig(Automation $workflow): array
    {
        return [
            'name' => $workflow->name,
            'isActive' => $workflow->isActive(),
            'trigger' => $workflow->trigger?->value ?? '',
            'conditions' => $workflow->conditions
                ->map(fn ($condition): array => [
                    'field' => $condition->field->value,
                    'operator' => $condition->operator->value,
                    'value' => $condition->valueText(),
                ])
                ->values()
                ->all(),
            'actions' => $workflow->actions
                ->map(fn ($action): array => [
                    'type' => $action->type->value,
                    'title' => is_string($action->config['title'] ?? null) ? $action->config['title'] : '',
                    'body' => is_string($action->config['body'] ?? null) ? $action->config['body'] : '',
                    'status' => is_string($action->config['status'] ?? null) ? $action->config['status'] : LeadStatus::Contacted->value,
                    'delay_hours' => isset($action->config['delay_hours']) ? (int) $action->config['delay_hours'] : 24,
                    'delay_minutes' => isset($action->config['delay_minutes']) ? (int) $action->config['delay_minutes'] : 60,
                ])
                ->values()
                ->all(),
            'triggers' => collect(AutomationTrigger::pickerCases())
                ->map(fn (AutomationTrigger $trigger): array => [
                    'value' => $trigger->value,
                    'label' => $trigger->label(),
                    'description' => $trigger->description(),
                    'ready' => $trigger->isReady(),
                ])
                ->values()
                ->all(),
            'triggerSections' => $this->pickerSections(AutomationTrigger::pickerCases()),
            'actionTypes' => collect($this->allowedActionTypes())
                ->map(fn (AutomationActionType $type): array => [
                    'value' => $type->value,
                    'label' => $type->label(),
                    'description' => $type->description(),
                    'selectable' => $type->isSelectable(),
                    'output' => $type->isOutput(),
                ])
                ->values()
                ->all(),
            'actionSections' => $this->pickerSections($this->allowedActionTypes()),
            'conditionFields' => collect(AutomationConditionField::cases())
                ->map(fn (AutomationConditionField $field): array => [
                    'value' => $field->value,
                    'label' => $field->label(),
                ])
                ->values()
                ->all(),
            'conditionOperators' => collect(AutomationConditionOperator::cases())
                ->map(fn (AutomationConditionOperator $operator): array => [
                    'value' => $operator->value,
                    'label' => $operator->label(),
                ])
                ->values()
                ->all(),
            'conditionValues' => $this->conditionValues(),
            'leadStatuses' => collect(LeadStatus::manuallySelectableCases())
                ->map(fn (LeadStatus $status): array => [
                    'value' => $status->value,
                    'label' => $status->label(),
                ])
                ->values()
                ->all(),
        ];
    }

    /**
     * @param  list<AutomationTrigger|AutomationActionType>  $cases
     * @return list<array{label: string, items: list<array{value: string, label: string, description: string, selectable: bool, comingSoon: bool}>}>
     */
    private function pickerSections(array $cases): array
    {
        $sections = [];

        foreach ($cases as $case) {
            $key = $case->pickerSection();

            $sections[$key] ??= [
                'label' => $case->pickerSectionLabel(),
                'items' => [],
            ];

            $selectable = $case instanceof AutomationTrigger
                ? $case->isReady()
                : $case->isSelectable();

            $sections[$key]['items'][] = [
                'value' => $case->value,
                'label' => $case->label(),
                'description' => $case->description(),
                'selectable' => $selectable,
                'comingSoon' => ! $selectable,
            ];
        }

        return array_values($sections);
    }

    /**
     * @return array<string, list<array{value: string, label: string}>>
     */
    private function conditionValues(): array
    {
        return [
            AutomationConditionField::Status->value => $this->enumOptions(LeadStatus::cases()),
            AutomationConditionField::Budget->value => $this->enumOptions(LeadBudget::cases()),
            AutomationConditionField::PropertyType->value => [
                ...$this->enumOptions(PropertyType::cases()),
                ...$this->propertyOptions(),
            ],
            AutomationConditionField::Source->value => $this->enumOptions(LeadSource::cases()),
            AutomationConditionField::Location->value => $this->stringOptions(
                Lead::query()
                    ->whereNotNull('location')
                    ->where('location', '!=', '')
                    ->distinct()
                    ->orderBy('location')
                    ->pluck('location')
                    ->concat(
                        Property::query()
                            ->whereNotNull('project_location')
                            ->where('project_location', '!=', '')
                            ->distinct()
                            ->orderBy('project_location')
                            ->pluck('project_location'),
                    ),
            ),
            AutomationConditionField::SiteVisitOutcome->value => $this->enumOptions(SiteVisitOutcome::cases()),
            AutomationConditionField::PropertyProject->value => $this->propertyOptions(),
        ];
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    private function propertyOptions(): array
    {
        return Property::query()
            ->whereNotNull('project_name')
            ->where('project_name', '!=', '')
            ->orderBy('project_name')
            ->get(['project_name', 'developer_name'])
            ->map(fn (Property $property): array => [
                'value' => $property->project_name,
                'label' => $property->listLabel(),
            ])
            ->unique('value')
            ->values()
            ->all();
    }

    /**
     * @param  list<LeadStatus|LeadBudget|PropertyType>  $cases
     * @return list<array{value: string, label: string}>
     */
    private function enumOptions(array $cases): array
    {
        return collect($cases)
            ->map(fn ($case): array => [
                'value' => $case->value,
                'label' => $case->label(),
            ])
            ->values()
            ->all();
    }

    /**
     * @return list<AutomationActionType>
     */
    private function allowedActionTypes(): array
    {
        $access = app(TenantPlanAccess::class);

        return array_values(array_filter(
            AutomationActionType::pickerCases(),
            function (AutomationActionType $type) use ($access): bool {
                $capability = PlanCapability::fromAutomationAction($type);

                return $capability === null || $access->hasCapability($capability);
            },
        ));
    }

    /**
     * @param  iterable<int, mixed>  $values
     * @return list<array{value: string, label: string}>
     */
    private function stringOptions(iterable $values): array
    {
        return collect($values)
            ->filter(fn (mixed $value): bool => is_string($value) && $value !== '')
            ->unique()
            ->sort()
            ->values()
            ->map(fn (string $value): array => [
                'value' => $value,
                'label' => $value,
            ])
            ->all();
    }
}
