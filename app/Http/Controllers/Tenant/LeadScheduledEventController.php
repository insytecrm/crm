<?php

namespace App\Http\Controllers\Tenant;

use App\Actions\CompleteFollowUpScheduledEvent;
use App\Actions\CompleteSiteVisitScheduledEvent;
use App\Actions\LogLeadActivity;
use App\Actions\RecordLeadScheduledEvent;
use App\Enums\LeadActivityType;
use App\Enums\LeadScheduledEventStatus;
use App\Enums\LeadScheduledEventType;
use App\Enums\ScheduledActivityContactMethod;
use App\Enums\ScheduledActivityNextStep;
use App\Enums\ScheduledActivityOutcome;
use App\Enums\ScheduledActivityPriority;
use App\Enums\SiteVisitNextStep;
use App\Enums\SiteVisitOutcome;
use App\Enums\SiteVisitType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\CompleteFollowUpScheduledEventRequest;
use App\Http\Requests\Tenant\CompleteSiteVisitScheduledEventRequest;
use App\Http\Requests\Tenant\RescheduleScheduledActivityRequest;
use App\Models\LeadScheduledEvent;
use Illuminate\Http\RedirectResponse;

class LeadScheduledEventController extends Controller
{
    public function reschedule(
        RescheduleScheduledActivityRequest $request,
        LeadScheduledEvent $scheduledEvent,
        RecordLeadScheduledEvent $recordLeadScheduledEvent,
        LogLeadActivity $logLeadActivity,
    ): RedirectResponse {
        if ($scheduledEvent->status !== LeadScheduledEventStatus::Scheduled) {
            return back()->with('status', __('Only scheduled activities can be rescheduled.'));
        }

        $scheduledAt = $request->date('scheduled_at');
        $notes = $request->validated('notes');
        $priority = $request->filled('priority')
            ? $request->enum('priority', ScheduledActivityPriority::class)
            : null;

        $event = $recordLeadScheduledEvent->reschedule(
            $scheduledEvent,
            $scheduledAt,
            $notes,
            $priority,
        );

        $lead = $event->lead;

        $description = filled($notes)
            ? __(':label rescheduled to :date — :notes', [
                'label' => $event->ordinalLabel(),
                'date' => $scheduledAt->format('M j, Y g:i A'),
                'notes' => $notes,
            ])
            : __(':label rescheduled to :date', [
                'label' => $event->ordinalLabel(),
                'date' => $scheduledAt->format('M j, Y g:i A'),
            ]);

        $activityType = match ($event->type) {
            LeadScheduledEventType::FollowUp => LeadActivityType::FollowUpScheduled,
            LeadScheduledEventType::SiteVisit => LeadActivityType::SiteVisitScheduled,
        };

        $metadata = [
            'scheduled_event_id' => $event->id,
            'sequence_number' => $event->sequence_number,
            'rescheduled' => true,
        ];

        if ($priority !== null) {
            $metadata['priority'] = $priority->value;
        }

        $logLeadActivity->handle(
            $lead,
            $activityType,
            $description,
            metadata: $metadata,
        );

        return back()->with('status', __('Activity rescheduled.'));
    }

    public function completeFollowUp(
        CompleteFollowUpScheduledEventRequest $request,
        LeadScheduledEvent $scheduledEvent,
        CompleteFollowUpScheduledEvent $completeFollowUpScheduledEvent,
    ): RedirectResponse {
        $completeFollowUpScheduledEvent->handle(
            $scheduledEvent,
            $request->enum('contact_method', ScheduledActivityContactMethod::class),
            $request->enum('outcome', ScheduledActivityOutcome::class),
            $request->enum('next_step', ScheduledActivityNextStep::class),
            $request->validated('notes'),
            $request->date('next_scheduled_at'),
            $request->filled('next_priority')
                ? $request->enum('next_priority', ScheduledActivityPriority::class)
                : null,
            $request->validated('next_notes'),
            $request->validated('task_title'),
            $request->date('task_due_at'),
            $request->filled('next_property_id') ? (int) $request->validated('next_property_id') : null,
            $request->filled('next_visit_type')
                ? $request->enum('next_visit_type', SiteVisitType::class)
                : null,
        );

        return back()->with('status', __('Follow-up completed.'));
    }

    public function completeSiteVisit(
        CompleteSiteVisitScheduledEventRequest $request,
        LeadScheduledEvent $scheduledEvent,
        CompleteSiteVisitScheduledEvent $completeSiteVisitScheduledEvent,
    ): RedirectResponse {
        $result = $completeSiteVisitScheduledEvent->handle(
            $scheduledEvent,
            (bool) $request->boolean('attended'),
            $request->filled('outcome')
                ? $request->enum('outcome', SiteVisitOutcome::class)
                : null,
            $request->enum('next_step', SiteVisitNextStep::class),
            $request->validated('notes'),
            $request->date('next_scheduled_at'),
            $request->filled('next_priority')
                ? $request->enum('next_priority', ScheduledActivityPriority::class)
                : null,
            $request->validated('next_notes'),
            $request->validated('task_title'),
            $request->date('task_due_at'),
            $request->filled('next_property_id') ? (int) $request->validated('next_property_id') : null,
            $request->filled('next_visit_type')
                ? $request->enum('next_visit_type', SiteVisitType::class)
                : null,
        );

        if ($result['open_booking']) {
            return back()->with('status', __('Site visit completed. Create a booking when ready.'));
        }

        return back()->with('status', __('Site visit completed.'));
    }
}
