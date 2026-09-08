<?php

namespace App\Actions;

use App\Enums\AutomationActionType;
use App\Enums\LeadActivityType;
use App\Enums\LeadScheduledEventStatus;
use App\Enums\LeadScheduledEventType;
use App\Enums\LeadStatus;
use App\Enums\PlanCapability;
use App\Enums\SiteVisitType;
use App\Enums\TaskStatus;
use App\Models\Automation;
use App\Models\AutomationAction;
use App\Models\Lead;
use App\Models\SalesTeam;
use App\Models\User;
use App\Support\Platform\TenantPlanAccess;

class PerformAutomationAction
{
    public function __construct(
        private LogLeadActivity $logLeadActivity,
        private RecordLeadScheduledEvent $recordLeadScheduledEvent,
        private TenantPlanAccess $planAccess,
    ) {}

    /**
     * @return array{ok: bool, detail: string, wait_minutes?: int}
     */
    public function handle(Automation $automation, AutomationAction $action, Lead $lead, User $owner, bool $dryRun): array
    {
        $capability = PlanCapability::fromAutomationAction($action->type);

        if ($capability !== null && ! $this->planAccess->hasCapability($capability)) {
            return [
                'ok' => false,
                'detail' => __('This action is not included in your plan.'),
            ];
        }

        if ($action->type->isOutput()) {
            return [
                'ok' => true,
                'detail' => __('Skipped :action until templates and channels are connected.', [
                    'action' => $action->type->label(),
                ]),
            ];
        }

        if (! $action->type->isSelectable()) {
            return [
                'ok' => false,
                'detail' => __('This action is not available yet.'),
            ];
        }

        return match ($action->type) {
            AutomationActionType::CreateTask => $this->createTask($automation, $action, $lead, $owner, $dryRun),
            AutomationActionType::AddNote => $this->addNote($automation, $action, $lead, $owner, $dryRun),
            AutomationActionType::ChangeStatus => $this->changeStatus($automation, $action, $lead, $owner, $dryRun),
            AutomationActionType::ScheduleFollowUp => $this->scheduleFollowUp($automation, $action, $lead, $owner, $dryRun),
            AutomationActionType::CreateSiteVisit => $this->createSiteVisit($automation, $action, $lead, $owner, $dryRun),
            AutomationActionType::RescheduleFollowUp => $this->rescheduleFollowUp($automation, $action, $lead, $owner, $dryRun),
            AutomationActionType::NotifySalesperson => $this->notifyUser(
                $automation,
                $action,
                $lead,
                $owner,
                $lead->assignedTo ?? $owner,
                __('Salesperson alert'),
                $dryRun,
            ),
            AutomationActionType::NotifyTeamLeader => $this->notifyUser(
                $automation,
                $action,
                $lead,
                $owner,
                $this->teamLeaderFor($lead),
                __('Team leader alert'),
                $dryRun,
            ),
            AutomationActionType::NotifyManager => $this->notifyUser(
                $automation,
                $action,
                $lead,
                $owner,
                $this->managerFor($lead) ?? $this->teamLeaderFor($lead),
                __('Manager alert'),
                $dryRun,
            ),
            AutomationActionType::MarkPriority => $this->setPriority($automation, $lead, $owner, true, $dryRun),
            AutomationActionType::RemovePriority => $this->setPriority($automation, $lead, $owner, false, $dryRun),
            AutomationActionType::Wait => $this->wait($action, $dryRun),
            default => [
                'ok' => false,
                'detail' => __('This action is not implemented yet.'),
            ],
        };
    }

    /**
     * @return array{ok: bool, detail: string}
     */
    private function setPriority(Automation $automation, Lead $lead, User $owner, bool $mark, bool $dryRun): array
    {
        $threshold = Lead::PRIORITY_SCORE_THRESHOLD;
        $current = (int) ($lead->lead_score ?? 0);
        $isPriority = $current >= $threshold;

        if ($mark && $isPriority) {
            return [
                'ok' => true,
                'detail' => __('Lead is already marked priority.'),
            ];
        }

        if (! $mark && ! $isPriority) {
            return [
                'ok' => true,
                'detail' => __('Lead is not marked priority.'),
            ];
        }

        if ($dryRun) {
            return [
                'ok' => true,
                'detail' => $mark
                    ? __('Would mark this lead as priority.')
                    : __('Would remove priority from this lead.'),
            ];
        }

        $lead->update([
            'lead_score' => $mark ? max($current, $threshold) : null,
        ]);

        $this->logLeadActivity->handle(
            $lead,
            LeadActivityType::NoteAdded,
            $mark ? __('Lead marked as priority') : __('Priority removed from lead'),
            $owner,
            [
                'source' => 'automation',
                'automation_id' => $automation->id,
                'lead_score' => $lead->lead_score,
                'priority' => $mark,
            ],
        );

        return [
            'ok' => true,
            'detail' => $mark
                ? __('Marked lead as priority.')
                : __('Removed priority from lead.'),
        ];
    }

    /**
     * @return array{ok: bool, detail: string}
     */
    private function createTask(Automation $automation, AutomationAction $action, Lead $lead, User $owner, bool $dryRun): array
    {
        $title = $this->configString($action, 'title') ?: __('Follow up');

        if ($dryRun) {
            return [
                'ok' => true,
                'detail' => __('Would create task: :title', ['title' => $title]),
            ];
        }

        $task = $lead->tasks()->create([
            'title' => $title,
            'description' => $this->configString($action, 'body') ?: null,
            'status' => TaskStatus::Pending,
            'assigned_to_id' => $owner->id,
            'created_by_id' => $owner->id,
        ]);

        $this->logLeadActivity->handle(
            $lead,
            LeadActivityType::TaskCreated,
            __('Task created: :title', ['title' => $task->title]),
            $owner,
            [
                'task_id' => $task->id,
                'source' => 'automation',
                'automation_id' => $automation->id,
            ],
        );

        return [
            'ok' => true,
            'detail' => __('Created task: :title', ['title' => $task->title]),
        ];
    }

    /**
     * @return array{ok: bool, detail: string}
     */
    private function addNote(Automation $automation, AutomationAction $action, Lead $lead, User $owner, bool $dryRun): array
    {
        $body = $this->configString($action, 'body') ?: __('Added by automation.');

        if ($dryRun) {
            return [
                'ok' => true,
                'detail' => __('Would add note: :body', ['body' => $body]),
            ];
        }

        $note = $lead->notes()->create([
            'body' => $body,
            'user_id' => $owner->id,
        ]);

        $this->logLeadActivity->handle(
            $lead,
            LeadActivityType::NoteAdded,
            __('Note added'),
            $owner,
            [
                'note_id' => $note->id,
                'body' => $note->body,
                'source' => 'automation',
                'automation_id' => $automation->id,
            ],
        );

        return [
            'ok' => true,
            'detail' => __('Added note.'),
        ];
    }

    /**
     * @return array{ok: bool, detail: string}
     */
    private function changeStatus(Automation $automation, AutomationAction $action, Lead $lead, User $owner, bool $dryRun): array
    {
        $status = LeadStatus::tryFrom($this->configString($action, 'status'));

        if ($status === null || $status->isClosed()) {
            return [
                'ok' => false,
                'detail' => __('Choose a valid open status for this action.'),
            ];
        }

        if ($lead->status === $status) {
            return [
                'ok' => true,
                'detail' => __('Status is already :status.', ['status' => $status->label()]),
            ];
        }

        if ($dryRun) {
            return [
                'ok' => true,
                'detail' => __('Would change status to :status', ['status' => $status->label()]),
            ];
        }

        $previousStatus = $lead->status;
        $lead->update(['status' => $status]);

        $this->logLeadActivity->handle(
            $lead,
            LeadActivityType::StatusChanged,
            __('Status changed from :from to :to', [
                'from' => $previousStatus->label(),
                'to' => $status->label(),
            ]),
            $owner,
            [
                'from' => $previousStatus->value,
                'to' => $status->value,
                'source' => 'automation',
                'automation_id' => $automation->id,
            ],
        );

        return [
            'ok' => true,
            'detail' => __('Changed status to :status', ['status' => $status->label()]),
        ];
    }

    /**
     * @return array{ok: bool, detail: string}
     */
    private function scheduleFollowUp(Automation $automation, AutomationAction $action, Lead $lead, User $owner, bool $dryRun): array
    {
        $hours = max(1, (int) ($action->config['delay_hours'] ?? 24));
        $scheduledAt = now()->addHours($hours);

        if ($dryRun) {
            return [
                'ok' => true,
                'detail' => __('Would schedule a follow-up for :date', [
                    'date' => $scheduledAt->format('M j, Y g:i A'),
                ]),
            ];
        }

        $event = $this->recordLeadScheduledEvent->schedule(
            $lead,
            LeadScheduledEventType::FollowUp,
            $scheduledAt,
            null,
            user: $owner,
        );

        $this->logLeadActivity->handle(
            $lead,
            LeadActivityType::FollowUpScheduled,
            __(':label scheduled for :date', [
                'label' => $event->ordinalLabel(),
                'date' => $scheduledAt->format('M j, Y g:i A'),
            ]),
            $owner,
            [
                'scheduled_event_id' => $event->id,
                'sequence_number' => $event->sequence_number,
                'source' => 'automation',
                'automation_id' => $automation->id,
            ],
        );

        return [
            'ok' => true,
            'detail' => __('Scheduled a follow-up for :date', [
                'date' => $scheduledAt->format('M j, Y g:i A'),
            ]),
        ];
    }

    /**
     * @return array{ok: bool, detail: string}
     */
    private function createSiteVisit(Automation $automation, AutomationAction $action, Lead $lead, User $owner, bool $dryRun): array
    {
        $hours = max(1, (int) ($action->config['delay_hours'] ?? 24));
        $scheduledAt = now()->addHours($hours);

        if ($dryRun) {
            return [
                'ok' => true,
                'detail' => __('Would schedule a site visit for :date', [
                    'date' => $scheduledAt->format('M j, Y g:i A'),
                ]),
            ];
        }

        $event = $this->recordLeadScheduledEvent->schedule(
            $lead,
            LeadScheduledEventType::SiteVisit,
            $scheduledAt,
            $this->configString($action, 'body') ?: null,
            visitType: SiteVisitType::FreshVisit,
            user: $owner,
        );

        $this->logLeadActivity->handle(
            $lead,
            LeadActivityType::SiteVisitScheduled,
            __(':label scheduled for :date', [
                'label' => $event->ordinalLabel(),
                'date' => $scheduledAt->format('M j, Y g:i A'),
            ]),
            $owner,
            [
                'scheduled_event_id' => $event->id,
                'sequence_number' => $event->sequence_number,
                'source' => 'automation',
                'automation_id' => $automation->id,
            ],
        );

        return [
            'ok' => true,
            'detail' => __('Scheduled a site visit for :date', [
                'date' => $scheduledAt->format('M j, Y g:i A'),
            ]),
        ];
    }

    /**
     * @return array{ok: bool, detail: string}
     */
    private function rescheduleFollowUp(Automation $automation, AutomationAction $action, Lead $lead, User $owner, bool $dryRun): array
    {
        $hours = max(1, (int) ($action->config['delay_hours'] ?? 24));
        $scheduledAt = now()->addHours($hours);

        $event = $lead->scheduledEvents()
            ->where('type', LeadScheduledEventType::FollowUp)
            ->where('status', LeadScheduledEventStatus::Scheduled)
            ->orderBy('scheduled_at')
            ->first();

        if ($event === null) {
            return $this->scheduleFollowUp($automation, $action, $lead, $owner, $dryRun);
        }

        if ($dryRun) {
            return [
                'ok' => true,
                'detail' => __('Would reschedule follow-up to :date', [
                    'date' => $scheduledAt->format('M j, Y g:i A'),
                ]),
            ];
        }

        $this->recordLeadScheduledEvent->reschedule($event, $scheduledAt);

        return [
            'ok' => true,
            'detail' => __('Rescheduled follow-up to :date', [
                'date' => $scheduledAt->format('M j, Y g:i A'),
            ]),
        ];
    }

    /**
     * @return array{ok: bool, detail: string}
     */
    private function notifyUser(
        Automation $automation,
        AutomationAction $action,
        Lead $lead,
        User $owner,
        ?User $assignee,
        string $fallbackTitle,
        bool $dryRun,
    ): array {
        if ($assignee === null) {
            return [
                'ok' => false,
                'detail' => __('No recipient was found for this alert.'),
            ];
        }

        $title = $this->configString($action, 'title') ?: $fallbackTitle;
        $body = $this->configString($action, 'body') ?: __('Automation alert for :name', ['name' => $lead->name]);

        if ($dryRun) {
            return [
                'ok' => true,
                'detail' => __('Would notify :name with task: :title', [
                    'name' => $assignee->name,
                    'title' => $title,
                ]),
            ];
        }

        $task = $lead->tasks()->create([
            'title' => $title,
            'description' => $body,
            'status' => TaskStatus::Pending,
            'assigned_to_id' => $assignee->id,
            'created_by_id' => $owner->id,
        ]);

        $this->logLeadActivity->handle(
            $lead,
            LeadActivityType::TaskCreated,
            __('Task created: :title', ['title' => $task->title]),
            $owner,
            [
                'task_id' => $task->id,
                'source' => 'automation',
                'automation_id' => $automation->id,
                'notify_user_id' => $assignee->id,
            ],
        );

        return [
            'ok' => true,
            'detail' => __('Notified :name.', ['name' => $assignee->name]),
        ];
    }

    /**
     * @return array{ok: bool, detail: string, wait_minutes?: int}
     */
    private function wait(AutomationAction $action, bool $dryRun): array
    {
        $minutes = $this->waitMinutes($action);

        return [
            'ok' => true,
            'detail' => $dryRun
                ? __('Would wait :minutes minutes before the next action.', ['minutes' => $minutes])
                : __('Waiting :minutes minutes before the next action.', ['minutes' => $minutes]),
            'wait_minutes' => $minutes,
        ];
    }

    private function waitMinutes(AutomationAction $action): int
    {
        $minutes = (int) ($action->config['delay_minutes'] ?? 0);
        $hours = (int) ($action->config['delay_hours'] ?? 0);

        $total = ($hours * 60) + $minutes;

        return max(1, $total > 0 ? $total : 60);
    }

    private function teamLeaderFor(Lead $lead): ?User
    {
        if ($lead->assigned_to_id === null) {
            return null;
        }

        $team = SalesTeam::query()
            ->active()
            ->whereHas('members', fn ($query) => $query->where('users.id', $lead->assigned_to_id))
            ->with('manager')
            ->first();

        return $team?->manager;
    }

    private function managerFor(Lead $lead): ?User
    {
        return $this->teamLeaderFor($lead);
    }

    private function configString(AutomationAction $action, string $key): string
    {
        $value = $action->config[$key] ?? null;

        return is_string($value) ? trim($value) : '';
    }
}
