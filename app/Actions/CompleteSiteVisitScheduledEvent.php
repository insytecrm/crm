<?php

namespace App\Actions;

use App\Enums\LeadActivityType;
use App\Enums\LeadScheduledEventStatus;
use App\Enums\LeadScheduledEventType;
use App\Enums\ScheduledActivityPriority;
use App\Enums\SiteVisitNextStep;
use App\Enums\SiteVisitOutcome;
use App\Enums\SiteVisitType;
use App\Enums\TaskStatus;
use App\Models\Lead;
use App\Models\LeadScheduledEvent;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;

class CompleteSiteVisitScheduledEvent
{
    public function __construct(
        private RecordLeadScheduledEvent $recordLeadScheduledEvent,
        private LogLeadActivity $logLeadActivity,
    ) {}

    /**
     * @return array{event: LeadScheduledEvent, open_booking: bool}
     */
    public function handle(
        LeadScheduledEvent $event,
        bool $attended,
        ?SiteVisitOutcome $outcome,
        SiteVisitNextStep $nextStep,
        ?string $notes = null,
        ?CarbonInterface $nextScheduledAt = null,
        ?ScheduledActivityPriority $nextPriority = null,
        ?string $nextNotes = null,
        ?string $taskTitle = null,
        ?CarbonInterface $taskDueAt = null,
        ?int $nextPropertyId = null,
        ?SiteVisitType $nextVisitType = null,
    ): array {
        if ($event->type !== LeadScheduledEventType::SiteVisit) {
            abort(404);
        }

        if ($event->status !== LeadScheduledEventStatus::Scheduled) {
            abort(422, __('Only scheduled site visits can be completed.'));
        }

        return DB::transaction(function () use (
            $event,
            $attended,
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
        ): array {
            $event->update([
                'attended' => $attended,
                'completion_outcome' => $attended ? $outcome : null,
                'next_step_type' => $nextStep,
                'completion_notes' => $notes,
            ]);

            $completedEvent = $this->recordLeadScheduledEvent->complete($event);
            $lead = $completedEvent->lead;

            $description = $attended && $outcome !== null
                ? __(':label completed — attended: :attended — :outcome', [
                    'label' => $completedEvent->ordinalLabel(),
                    'attended' => __('Yes'),
                    'outcome' => $outcome->label(),
                ])
                : __(':label completed — attended: :attended', [
                    'label' => $completedEvent->ordinalLabel(),
                    'attended' => $attended ? __('Yes') : __('No'),
                ]);

            if (filled($notes)) {
                $description .= ' — '.$notes;
            }

            $this->logLeadActivity->handle(
                $lead,
                LeadActivityType::SiteVisitCompleted,
                $description,
                metadata: [
                    'scheduled_event_id' => $completedEvent->id,
                    'sequence_number' => $completedEvent->sequence_number,
                    'attended' => $attended,
                    'outcome' => $attended ? $outcome?->value : null,
                    'next_step' => $nextStep->value,
                    'completion_notes' => $notes,
                ],
            );

            $openBooking = false;

            match ($nextStep) {
                SiteVisitNextStep::ScheduleFollowUp => $this->scheduleFollowUp(
                    $lead,
                    $nextScheduledAt,
                    $nextPriority ?? ScheduledActivityPriority::Normal,
                    $nextNotes,
                ),
                SiteVisitNextStep::ScheduleSiteVisit => $this->scheduleSiteVisit(
                    $lead,
                    $nextScheduledAt,
                    $nextNotes,
                    $nextPropertyId,
                    $nextVisitType,
                ),
                SiteVisitNextStep::CreateTask => $this->createTask(
                    $lead,
                    $taskTitle,
                    $taskDueAt,
                    $nextNotes,
                ),
                SiteVisitNextStep::CreateBooking => $openBooking = $this->markCreateBooking($lead),
                SiteVisitNextStep::None => $lead->update(['next_action' => null]),
            };

            return [
                'event' => $completedEvent->fresh(),
                'open_booking' => $openBooking,
            ];
        });
    }

    private function markCreateBooking(Lead $lead): bool
    {
        $lead->update(['next_action' => __('Create Booking')]);

        return true;
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
