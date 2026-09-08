<?php

namespace App\Actions;

use App\Enums\LeadActivityType;
use App\Enums\LeadScheduledEventStatus;
use App\Enums\LeadScheduledEventType;
use App\Models\Lead;
use App\Models\LeadScheduledEvent;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class MergeLeads
{
    public function __construct(private LogLeadActivity $logLeadActivity) {}

    /**
     * @param  list<int>  $duplicateLeadIds
     */
    public function handle(Lead $primaryLead, array $duplicateLeadIds, ?User $user = null): Lead
    {
        $duplicateLeadIds = array_values(array_unique(array_map('intval', $duplicateLeadIds)));
        $duplicateLeadIds = array_values(array_filter(
            $duplicateLeadIds,
            fn (int $leadId): bool => $leadId !== $primaryLead->id,
        ));

        if ($duplicateLeadIds === []) {
            throw new InvalidArgumentException(__('Select at least one duplicate lead to merge.'));
        }

        $duplicateLeads = Lead::query()
            ->whereIn('id', $duplicateLeadIds)
            ->get();

        if ($duplicateLeads->count() !== count($duplicateLeadIds)) {
            throw new InvalidArgumentException(__('One or more selected leads could not be found.'));
        }

        DB::transaction(function () use ($primaryLead, $duplicateLeads, $user): void {
            foreach ($duplicateLeads as $duplicateLead) {
                $this->moveRelatedRecords($primaryLead, $duplicateLead);
                $this->mergeAttributes($primaryLead, $duplicateLead);
                $duplicateLead->delete();
            }

            $this->renumberScheduledEvents($primaryLead);
            $this->syncScheduledDates($primaryLead);
            $primaryLead->save();

            $mergedNames = $duplicateLeads->pluck('name')->implode(', ');

            $this->logLeadActivity->handle(
                $primaryLead->fresh(),
                LeadActivityType::LeadMerged,
                __('Merged duplicate leads: :names', ['names' => $mergedNames]),
                $user,
                [
                    'merged_lead_ids' => $duplicateLeads->pluck('id')->all(),
                ],
            );
        });

        return $primaryLead->fresh([
            'assignedTo',
            'createdBy',
        ]);
    }

    private function moveRelatedRecords(Lead $primaryLead, Lead $duplicateLead): void
    {
        $duplicateLead->activities()->update(['lead_id' => $primaryLead->id]);
        $duplicateLead->tasks()->update(['lead_id' => $primaryLead->id]);
        $duplicateLead->notes()->update(['lead_id' => $primaryLead->id]);
        $duplicateLead->documents()->update(['lead_id' => $primaryLead->id]);
        $duplicateLead->scheduledEvents()->update(['lead_id' => $primaryLead->id]);

        if ($primaryLead->hasBooking()) {
            $duplicateLead->bookings()->update(['lead_id' => null]);
        } else {
            $duplicateLead->bookings()->update(['lead_id' => $primaryLead->id]);
        }
    }

    private function mergeAttributes(Lead $primaryLead, Lead $duplicateLead): void
    {
        $fillableFields = [
            'phone',
            'email',
            'source',
            'sub_source',
            'source_context',
            'budget',
            'location',
            'property_type',
            'configuration',
            'assigned_to_id',
            'next_action',
        ];

        foreach ($fillableFields as $field) {
            if ($this->isBlank($primaryLead->{$field}) && ! $this->isBlank($duplicateLead->{$field})) {
                $primaryLead->{$field} = $duplicateLead->{$field};
            }
        }

        $primaryLead->lead_score = max(
            (int) $primaryLead->lead_score,
            (int) $duplicateLead->lead_score,
        );

        if ($duplicateLead->last_activity_at !== null) {
            $primaryLastActivity = $primaryLead->last_activity_at;

            if ($primaryLastActivity === null || $duplicateLead->last_activity_at->gt($primaryLastActivity)) {
                $primaryLead->last_activity_at = $duplicateLead->last_activity_at;
            }
        }
    }

    private function renumberScheduledEvents(Lead $primaryLead): void
    {
        foreach (LeadScheduledEventType::cases() as $type) {
            $events = LeadScheduledEvent::query()
                ->where('lead_id', $primaryLead->id)
                ->where('type', $type)
                ->orderBy('scheduled_at')
                ->orderBy('id')
                ->get();

            foreach ($events as $index => $event) {
                $event->update(['sequence_number' => $index + 1]);
            }
        }
    }

    private function syncScheduledDates(Lead $primaryLead): void
    {
        foreach (LeadScheduledEventType::cases() as $type) {
            $latestScheduledEvent = LeadScheduledEvent::query()
                ->where('lead_id', $primaryLead->id)
                ->where('type', $type)
                ->where('status', LeadScheduledEventStatus::Scheduled)
                ->orderByDesc('sequence_number')
                ->first();

            $leadField = match ($type) {
                LeadScheduledEventType::FollowUp => 'next_follow_up_at',
                LeadScheduledEventType::SiteVisit => 'upcoming_site_visit_at',
            };

            $primaryLead->{$leadField} = $latestScheduledEvent?->scheduled_at;
        }
    }

    private function isBlank(mixed $value): bool
    {
        return $value === null || $value === '';
    }
}
