<?php

namespace App\Actions;

use App\Enums\LeadActivityType;
use App\Enums\LeadScheduledEventStatus;
use App\Enums\LeadScheduledEventType;
use App\Enums\LeadStatus;
use App\Enums\ScheduledActivityContactMethod;
use App\Enums\ScheduledActivityNextStep;
use App\Enums\ScheduledActivityOutcome;
use App\Enums\ScheduledActivityPriority;
use App\Enums\SiteVisitType;
use App\Enums\TaskStatus;
use App\Models\Lead;
use App\Models\LeadScheduledEvent;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;

class CompleteFollowUpScheduledEvent
{
    public function __construct(
        private RecordLeadScheduledEvent $recordLeadScheduledEvent,
        private LogLeadActivity $logLeadActivity,
    ) {}

    public function handle(
        LeadScheduledEvent $event,
        ScheduledActivityContactMethod $contactMethod,
        ScheduledActivityOutcome $outcome,
        ScheduledActivityNextStep $nextStep,
        ?string $notes = null,
        ?CarbonInterface $nextScheduledAt = null,
        ?ScheduledActivityPriority $nextPriority = null,
        ?string $nextNotes = null,
        ?string $taskTitle = null,
        ?CarbonInterface $taskDueAt = null,
        ?int $nextPropertyId = null,
        ?SiteVisitType $nextVisitType = null,
    ): LeadScheduledEvent {
        if ($event->type !== LeadScheduledEventType::FollowUp) {
            abort(404);
        }

        if ($event->status !== LeadScheduledEventStatus::Scheduled) {
            abort(422, __('Only scheduled follow-ups can be completed.'));
        }

        return DB::transaction(function () use (
            $event,
            $contactMethod,
            $outcome,
            $nextStep,
            $notes,
            $nextScheduledAt,
            $nextPriority,
            $nextNotes,
            $taskTitle,
            $taskDueAt,
            $nextPropertyId,
            $nextVisitType,
        ): LeadScheduledEvent {
            $event->update([
                'completion_method' => $contactMethod,
                'completion_outcome' => $outcome,
                'next_step_type' => $nextStep,
                'completion_notes' => $notes,
            ]);

            $completedEvent = $this->recordLeadScheduledEvent->complete($event);
            $lead = $completedEvent->lead;

            $this->applyFirstFollowUpStatus($lead, $completedEvent, $outcome);

            $description = __(':label completed via :method — :outcome', [
                'label' => $completedEvent->ordinalLabel(),
                'method' => $contactMethod->label(),
                'outcome' => $outcome->label(),
            ]);

            if (filled($notes)) {
                $description .= ' — '.$notes;
            }

            $this->logLeadActivity->handle(
                $lead,
                LeadActivityType::FollowUpCompleted,
                $description,
                metadata: [
                    'scheduled_event_id' => $completedEvent->id,
                    'sequence_number' => $completedEvent->sequence_number,
                    'contact_method' => $contactMethod->value,
                    'outcome' => $outcome->value,
                    'next_step' => $nextStep->value,
                    'completion_notes' => $notes,
                ],
            );

            match ($nextStep) {
                ScheduledActivityNextStep::ScheduleFollowUp => $this->scheduleFollowUp(
                    $lead,
                    $nextScheduledAt,
                    $nextPriority ?? ScheduledActivityPriority::Normal,
                    $nextNotes,
                ),
                ScheduledActivityNextStep::ScheduleSiteVisit => $this->scheduleSiteVisit(
                    $lead,
                    $nextScheduledAt,
                    $nextNotes,
                    $nextPropertyId,
                    $nextVisitType,
                ),
                ScheduledActivityNextStep::CreateTask => $this->createTask(
                    $lead,
                    $taskTitle,
                    $taskDueAt,
                    $nextNotes,
                ),
                ScheduledActivityNextStep::None => $lead->update(['next_action' => null]),
            };

            return $completedEvent->fresh();
        });
    }

    private function applyFirstFollowUpStatus(
        Lead $lead,
        LeadScheduledEvent $completedEvent,
        ScheduledActivityOutcome $outcome,
    ): void {
        if ($completedEvent->sequence_number !== 1) {
            return;
        }

        if (
            $outcome === ScheduledActivityOutcome::Interested
            && in_array($lead->status, [LeadStatus::New, LeadStatus::Contacted], true)
        ) {
            $lead->update(['status' => LeadStatus::Qualified]);
        }
    }

    private function scheduleFollowUp(
        Lead $lead,
        ?CarbonInterface $scheduledAt,
        ScheduledActivityPriority $priority,
        ?string $notes,
    ): void {
        if ($scheduledAt === null) {
            return;
        }

        $newEvent = $this->recordLeadScheduledEvent->schedule(
            $lead,
            LeadScheduledEventType::FollowUp,
            $scheduledAt,
            $notes,
            $priority,
        );

        $scheduleDescription = filled($notes)
            ? __(':label scheduled for :date — :notes', [
                'label' => $newEvent->ordinalLabel(),
                'date' => $scheduledAt->format('M j, Y g:i A'),
                'notes' => $notes,
            ])
            : __(':label scheduled for :date', [
                'label' => $newEvent->ordinalLabel(),
                'date' => $scheduledAt->format('M j, Y g:i A'),
            ]);

        $this->logLeadActivity->handle(
            $lead,
            LeadActivityType::FollowUpScheduled,
            $scheduleDescription,
            metadata: [
                'scheduled_event_id' => $newEvent->id,
                'sequence_number' => $newEvent->sequence_number,
                'priority' => $priority->value,
            ],
        );
    }

    private function scheduleSiteVisit(
        Lead $lead,
        ?CarbonInterface $scheduledAt,
        ?string $notes,
        ?int $propertyId = null,
        ?SiteVisitType $visitType = null,
    ): void {
        if ($scheduledAt === null) {
            return;
        }

        $newEvent = $this->recordLeadScheduledEvent->schedule(
            $lead,
            LeadScheduledEventType::SiteVisit,
            $scheduledAt,
            $notes,
            propertyId: $propertyId,
            visitType: $visitType,
        );

        $scheduleDescription = filled($notes)
            ? __(':label scheduled for :date — :notes', [
                'label' => $newEvent->ordinalLabel(),
                'date' => $scheduledAt->format('M j, Y g:i A'),
                'notes' => $notes,
            ])
            : __(':label scheduled for :date', [
                'label' => $newEvent->ordinalLabel(),
                'date' => $scheduledAt->format('M j, Y g:i A'),
            ]);

        $this->logLeadActivity->handle(
            $lead,
            LeadActivityType::SiteVisitScheduled,
            $scheduleDescription,
            metadata: [
                'scheduled_event_id' => $newEvent->id,
                'sequence_number' => $newEvent->sequence_number,
                'property_id' => $propertyId,
                'visit_type' => $visitType?->value,
            ],
        );
    }

    private function createTask(
        Lead $lead,
        ?string $taskTitle,
        ?CarbonInterface $taskDueAt,
        ?string $notes,
    ): void {
        if ($taskTitle === null || $taskTitle === '') {
            return;
        }

        $task = $lead->tasks()->create([
            'title' => $taskTitle,
            'description' => $notes,
            'due_at' => $taskDueAt,
            'status' => TaskStatus::Pending,
            'assigned_to_id' => auth()->id(),
            'created_by_id' => auth()->id(),
        ]);

        $this->logLeadActivity->handle(
            $lead,
            LeadActivityType::TaskCreated,
            __('Task created: :title', ['title' => $task->title]),
            metadata: ['task_id' => $task->id],
        );
    }
}
