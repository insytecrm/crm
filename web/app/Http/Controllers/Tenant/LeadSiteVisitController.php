<?php

namespace App\Http\Controllers\Tenant;

use App\Actions\LogLeadActivity;
use App\Actions\RecordLeadScheduledEvent;
use App\Enums\LeadActivityType;
use App\Enums\LeadScheduledEventType;
use App\Enums\LeadStatus;
use App\Enums\SiteVisitType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\CompleteScheduledActivityRequest;
use App\Http\Requests\Tenant\ScheduleSiteVisitRequest;
use App\Models\Lead;
use App\Support\LeadDrawerRedirect;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class LeadSiteVisitController extends Controller
{
    public function store(
        ScheduleSiteVisitRequest $request,
        Lead $lead,
        RecordLeadScheduledEvent $recordLeadScheduledEvent,
        LogLeadActivity $logLeadActivity,
    ): RedirectResponse {
        $notes = $request->validated('notes');
        $propertyId = (int) $request->validated('property_id');
        $visitType = $request->enum('visit_type', SiteVisitType::class);

        $event = DB::transaction(function () use ($request, $lead, $recordLeadScheduledEvent, $notes, $propertyId, $visitType) {
            $lead->update([
                'upcoming_site_visit_at' => $request->validated('upcoming_site_visit_at'),
                'status' => $lead->status === LeadStatus::New ? LeadStatus::SiteVisit : $lead->status,
                'next_action' => __('Site Visit'),
            ]);

            return $recordLeadScheduledEvent->schedule(
                $lead,
                LeadScheduledEventType::SiteVisit,
                $lead->upcoming_site_visit_at,
                $notes,
                propertyId: $propertyId,
                visitType: $visitType,
            );
        });

        $description = $notes
            ? __(':label scheduled for :date — :notes', [
                'label' => $event->ordinalLabel(),
                'date' => $lead->upcoming_site_visit_at?->format('M j, Y g:i A'),
                'notes' => $notes,
            ])
            : __(':label scheduled for :date', [
                'label' => $event->ordinalLabel(),
                'date' => $lead->upcoming_site_visit_at?->format('M j, Y g:i A'),
            ]);

        $logLeadActivity->handle(
            $lead,
            LeadActivityType::SiteVisitScheduled,
            $description,
            metadata: [
                'scheduled_event_id' => $event->id,
                'sequence_number' => $event->sequence_number,
                'property_id' => $propertyId,
                'visit_type' => $visitType->value,
            ],
        );

        return LeadDrawerRedirect::to($lead, __('Site visit scheduled.'));
    }

    public function complete(
        CompleteScheduledActivityRequest $request,
        Lead $lead,
        RecordLeadScheduledEvent $recordLeadScheduledEvent,
        LogLeadActivity $logLeadActivity,
    ): RedirectResponse {
        if ($lead->upcoming_site_visit_at === null) {
            return back()->with('status', __('No site visit scheduled for this lead.'));
        }

        $notes = $request->validated('notes');
        $event = $recordLeadScheduledEvent->completeLatest($lead, LeadScheduledEventType::SiteVisit);

        $description = match (true) {
            $event !== null && filled($notes) => __(':label completed — :notes', [
                'label' => $event->ordinalLabel(),
                'notes' => $notes,
            ]),
            $event !== null => __(':label completed', ['label' => $event->ordinalLabel()]),
            filled($notes) => __('Site visit completed — :notes', ['notes' => $notes]),
            default => __('Site visit completed'),
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
            LeadActivityType::SiteVisitCompleted,
            $description,
            metadata: $metadata,
        );

        return LeadDrawerRedirect::to($lead, __('Site visit marked complete.'));
    }
}
