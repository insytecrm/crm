<?php

namespace App\Jobs;

use App\Actions\PerformAutomationAction;
use App\Enums\AutomationActionType;
use App\Enums\AutomationRunStatus;
use App\Models\Automation;
use App\Models\AutomationRun;
use App\Models\Lead;
use App\Support\AutomationRuntime;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Throwable;

class ContinueAutomationAfterWait implements ShouldQueue
{
    use Queueable;

    /**
     * @param  list<int>  $actionIds
     */
    public function __construct(
        public int $automationId,
        public int $leadId,
        public array $actionIds,
    ) {}

    public function handle(PerformAutomationAction $performAutomationAction): void
    {
        $automation = Automation::query()->with(['actions', 'user'])->find($this->automationId);
        $lead = Lead::query()->withoutGlobalScopes()->find($this->leadId);

        if ($automation === null || $lead === null || $lead->isClosed() || ! $automation->isActive()) {
            return;
        }

        if ($lead->assigned_to_id !== $automation->user_id) {
            return;
        }

        $owner = $automation->user;

        if ($owner === null) {
            return;
        }

        $actions = $automation->actions->whereIn('id', $this->actionIds)->sortBy('sort_order')->values();
        $steps = [];
        $failed = false;

        AutomationRuntime::run(function () use ($performAutomationAction, $automation, $actions, $lead, $owner, &$steps, &$failed): void {
            foreach ($actions as $action) {
                if ($action->type === AutomationActionType::Wait) {
                    continue;
                }

                $lead->refresh();

                try {
                    $performed = $performAutomationAction->handle($automation, $action, $lead, $owner, false);
                } catch (Throwable $exception) {
                    report($exception);
                    $performed = [
                        'ok' => false,
                        'detail' => __('This action could not be completed.'),
                    ];
                }

                $steps[] = [
                    'type' => $action->type->value,
                    'ok' => $performed['ok'],
                    'detail' => $performed['detail'],
                ];

                if (! $performed['ok']) {
                    $failed = true;
                    break;
                }
            }
        });

        AutomationRun::query()->create([
            'automation_id' => $automation->id,
            'user_id' => $automation->user_id,
            'lead_id' => $lead->id,
            'trigger' => $automation->trigger,
            'status' => $failed ? AutomationRunStatus::Failed : AutomationRunStatus::Succeeded,
            'dry_run' => false,
            'context' => [
                'lead_id' => $lead->id,
                'continued_after_wait' => true,
            ],
            'result' => [
                'message' => $failed
                    ? __('This workflow stopped because an action failed.')
                    : __('Workflow continued after wait.'),
                'steps' => $steps,
            ],
        ]);

        if (! $failed) {
            $automation->update(['last_run_at' => now()]);
        }
    }
}
