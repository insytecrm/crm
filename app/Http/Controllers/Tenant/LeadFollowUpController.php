<?php

namespace App\Http\Controllers\Tenant;

use App\Actions\LogLeadActivity;
use App\Actions\RecordLeadScheduledEvent;
use App\Enums\LeadActivityType;
use App\Enums\LeadScheduledEventType;
use App\Enums\ScheduledActivityPriority;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\CompleteScheduledActivityRequest;
use App\Http\Requests\Tenant\ScheduleFollowUpRequest;
use App\Models\Lead;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class LeadFollowUpController extends Controller
{
    public function store(
        ScheduleFollowUpRequest $request,
        Lead $lead,
        RecordLeadScheduledEvent $recordLeadScheduledEvent,
        LogLeadActivity $logLeadActivity,
    ): RedirectResponse {
        $notes = $request->validated('notes');

        $event = DB::transaction(function () use ($request, $lead, $recordLeadScheduledEvent, $notes) {
            $priority = $request->enum('priority', ScheduledActivityPriority::class);

            $lead->update([
                'next_follow_up_at' => $request->validated('next_follow_up_at'),
                'next_action' => __('Follow-up'),
            ]);

            return $recordLeadScheduledEvent->schedule(
                $lead,
                LeadScheduledEventType::FollowUp,
                $lead->next_follow_up_at,
                $notes,
                $priority,
            );
        });

        $description = $notes
            ? __(':label scheduled for :date — :notes', [
                'label' => $event->ordinalLabel(),
                'date' => $lead->next_follow_up_at?->format('M j, Y g:i A'),
                'notes' => $notes,
            ])
            : __(':label scheduled for :date', [
                'label' => $event->ordinalLabel(),
                'date' => $lead->next_follow_up_at?->format('M j, Y g:i A'),
            ]);

        $logLeadActivity->handle(
            $lead,
            LeadActivityType::FollowUpScheduled,
            $description,
            metadata: [
                'scheduled_event_id' => $event->id,
                'sequence_number' => $event->sequence_number,
                'priority' => $request->enum('priority', ScheduledActivityPriority::class)->value,
            ],
        );

        return back()->with('status', __('Follow-up scheduled.'));
    }

    public function complete(
        CompleteScheduledActivityRequest $request,
        Lead $lead,
        RecordLeadScheduledEvent $recordLeadScheduledEvent,
        LogLeadActivity $logLeadActivity,
    ): RedirectResponse {
        if ($lead->next_follow_up_at === null) {
            return back()->with('status', __('No follow-up scheduled for this lead.'));
        }

        $notes = $request->validated('notes');
        $event = $recordLeadScheduledEvent->completeLatest($lead, LeadScheduledEventType::FollowUp);

        $description = match (true) {
            $event !== null && filled($notes) => __(':label completed — :notes', [
                'label' => $event->ordinalLabel(),
                'notes' => $notes,
            ]),
            $event !== null => __(':label completed', ['label' => $event->ordinalLabel()]),
            filled($notes) => __('Follow-up completed — :notes', ['notes' => $notes]),
            default => __('Follow-up completed'),
        };

        $metadata = $event ? [
            'scheduled_event_id' => $event->id,
            'sequence_number' => $event->sequence_number,
        ] : [];

        if (filled($notes)) {
            $metadata['completion_notes'] = $notes;
        }

        $logLeadActivity->handle(
            $lead,
            LeadActivityType::FollowUpCompleted,
            $description,
            metadata: $metadata,
        );

        return back()->with('status', __('Follow-up marked complete.'));
    }
}
