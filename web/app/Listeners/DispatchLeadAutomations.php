<?php

namespace App\Listeners;

use App\Actions\MatchLeadAutomations;
use App\Actions\RunAutomation;
use App\Enums\AutomationRunStatus;
use App\Enums\AutomationTrigger;
use App\Events\LeadActivityRecorded;
use App\Models\Lead;
use App\Support\AutomationRuntime;
use Throwable;

class DispatchLeadAutomations
{
    public function __construct(
        private MatchLeadAutomations $matchLeadAutomations,
        private RunAutomation $runAutomation,
    ) {}

    public function handle(LeadActivityRecorded $event): void
    {
        if (AutomationRuntime::isRunning()) {
            return;
        }

        $trigger = AutomationTrigger::fromActivity($event->activity->type);

        if ($trigger === null) {
            return;
        }

        $lead = Lead::query()->withoutGlobalScopes()->find($event->activity->lead_id);

        if ($lead === null) {
            return;
        }

        $metadata = is_array($event->activity->metadata) ? $event->activity->metadata : [];

        foreach ($this->matchLeadAutomations->handle($lead, $trigger) as $automation) {
            try {
                $this->runAutomation->handle($automation, [
                    'lead_id' => $lead->id,
                    'activity_id' => $event->activity->id,
                    'metadata' => $metadata,
                ], false);
            } catch (Throwable $exception) {
                report($exception);

                $automation->runs()->create([
                    'user_id' => $automation->user_id,
                    'lead_id' => $lead->id,
                    'trigger' => $automation->trigger,
                    'status' => AutomationRunStatus::Failed,
                    'dry_run' => false,
                    'context' => [
                        'lead_id' => $lead->id,
                        'activity_id' => $event->activity->id,
                    ],
                    'result' => [
                        'message' => __('This workflow could not run.'),
                    ],
                ]);
            }
        }
    }
}
