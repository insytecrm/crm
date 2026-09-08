<?php

namespace App\Actions;

use App\Enums\AutomationActionType;
use App\Enums\AutomationRunStatus;
use App\Enums\PlanLimitKey;
use App\Jobs\ContinueAutomationAfterWait;
use App\Models\Automation;
use App\Models\AutomationRun;
use App\Models\Lead;
use App\Models\User;
use App\Support\AutomationRuntime;
use Throwable;

class RunAutomation
{
    public function __construct(
        private EvaluateAutomationConditions $evaluateAutomationConditions,
        private PerformAutomationAction $performAutomationAction,
        private AssertPlanLimit $assertPlanLimit,
    ) {}

    /**
     * @param  array<string, mixed>  $context
     */
    public function handle(Automation $automation, array $context = [], bool $dryRun = true): AutomationRun
    {
        if (! $dryRun) {
            $this->assertPlanLimit->handle(PlanLimitKey::AutomationRunsMonthly);
        }

        $automation->loadMissing(['conditions', 'actions', 'user']);

        $leadId = isset($context['lead_id']) ? (int) $context['lead_id'] : null;
        $metadata = is_array($context['metadata'] ?? null) ? $context['metadata'] : [];
        $lead = $leadId ? Lead::query()->withoutGlobalScopes()->find($leadId) : null;

        [$status, $result] = $this->execute($automation, $lead, $metadata, $dryRun);

        $run = $automation->runs()->create([
            'user_id' => $automation->user_id,
            'lead_id' => $lead?->id,
            'trigger' => $automation->trigger,
            'status' => $status,
            'dry_run' => $dryRun,
            'context' => $context === [] ? null : $context,
            'result' => $result,
        ]);

        if (! $dryRun && $status === AutomationRunStatus::Succeeded) {
            $automation->update(['last_run_at' => now()]);
        }

        return $run;
    }

    /**
     * @param  array<string, mixed>  $metadata
     * @return array{0: AutomationRunStatus, 1: array<string, mixed>}
     */
    private function execute(Automation $automation, ?Lead $lead, array $metadata, bool $dryRun): array
    {
        if ($lead === null) {
            return $this->outcome(
                AutomationRunStatus::Skipped,
                __('Choose a lead to test this workflow.'),
            );
        }

        if ($lead->isClosed()) {
            return $this->outcome(
                AutomationRunStatus::Skipped,
                __('This workflow skipped a closed lead.'),
            );
        }

        if (! $dryRun && ! $automation->isActive()) {
            return $this->outcome(
                AutomationRunStatus::Skipped,
                __('This workflow is turned off.'),
            );
        }

        if (! $dryRun && $lead->assigned_to_id !== $automation->user_id) {
            return $this->outcome(
                AutomationRunStatus::Skipped,
                __('This workflow only runs for leads assigned to its owner.'),
            );
        }

        $evaluation = $this->evaluateAutomationConditions->handle($automation, $lead, $metadata);

        if (! $evaluation['passed']) {
            return $this->outcome(
                AutomationRunStatus::Skipped,
                __('Conditions did not match.'),
                ['checks' => $evaluation['checks']],
            );
        }

        $owner = $automation->user;

        if (! $owner instanceof User) {
            return $this->outcome(
                AutomationRunStatus::Failed,
                __('This workflow has no owner.'),
                ['checks' => $evaluation['checks']],
            );
        }

        $steps = [];
        $failed = false;
        $deferred = false;

        $perform = function () use ($automation, $lead, $owner, $dryRun, &$steps, &$failed, &$deferred): void {
            $actions = $automation->actions->values();

            foreach ($actions as $index => $action) {
                $lead->refresh();

                try {
                    $performed = $this->performAutomationAction->handle(
                        $automation,
                        $action,
                        $lead,
                        $owner,
                        $dryRun,
                    );
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

                if ($action->type === AutomationActionType::Wait && ! $dryRun) {
                    $remainingIds = $actions->slice($index + 1)
                        ->pluck('id')
                        ->map(fn ($id): int => (int) $id)
                        ->values()
                        ->all();

                    if ($remainingIds !== []) {
                        ContinueAutomationAfterWait::dispatch(
                            $automation->id,
                            $lead->id,
                            $remainingIds,
                        )->delay(now()->addMinutes((int) ($performed['wait_minutes'] ?? 60)));

                        $deferred = true;
                        break;
                    }
                }
            }
        };

        if ($dryRun) {
            $perform();
        } else {
            AutomationRuntime::run($perform);
        }

        if ($failed) {
            return $this->outcome(
                AutomationRunStatus::Failed,
                __('This workflow stopped because an action failed.'),
                [
                    'checks' => $evaluation['checks'],
                    'steps' => $steps,
                ],
            );
        }

        $message = $dryRun
            ? ($steps === []
                ? __('This workflow has no actions to run.')
                : collect($steps)->pluck('detail')->filter()->implode(' '))
            : ($steps === []
                ? __('This workflow has no actions to run.')
                : ($deferred
                    ? __('Workflow ran and scheduled the remaining actions after the wait.')
                    : __('Workflow ran successfully.')));

        return $this->outcome(
            $dryRun ? AutomationRunStatus::Tested : AutomationRunStatus::Succeeded,
            $message,
            [
                'checks' => $evaluation['checks'],
                'steps' => $steps,
            ],
        );
    }

    /**
     * @param  array<string, mixed>  $extra
     * @return array{0: AutomationRunStatus, 1: array<string, mixed>}
     */
    private function outcome(AutomationRunStatus $status, string $message, array $extra = []): array
    {
        return [
            $status,
            array_merge(['message' => $message], $extra),
        ];
    }
}
