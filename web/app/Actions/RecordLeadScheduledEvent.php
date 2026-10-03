<?php

namespace App\Actions;

use App\Enums\LeadScheduledEventStatus;
use App\Enums\LeadScheduledEventType;
use App\Enums\LeadStatus;
use App\Enums\ScheduledActivityPriority;
use App\Enums\SiteVisitType;
use App\Models\Lead;
use App\Models\LeadScheduledEvent;
use App\Models\User;
use Carbon\CarbonInterface;

class RecordLeadScheduledEvent
{
    public function schedule(
        Lead $lead,
        LeadScheduledEventType $type,
        CarbonInterface $scheduledAt,
        ?string $notes = null,
        ScheduledActivityPriority $priority = ScheduledActivityPriority::Normal,
        ?int $propertyId = null,
        ?SiteVisitType $visitType = null,
        ?User $user = null,
    ): LeadScheduledEvent {
        $sequenceNumber = (int) LeadScheduledEvent::query()
            ->where('lead_id', $lead->id)
            ->where('type', $type)
            ->max('sequence_number') + 1;

        $event = $lead->scheduledEvents()->create([
            'type' => $type,
            'sequence_number' => $sequenceNumber,
            'scheduled_at' => $scheduledAt,
            'priority' => $priority,
            'property_id' => $propertyId,
            'visit_type' => $type === LeadScheduledEventType::SiteVisit ? $visitType : null,
            'notes' => $notes,
            'status' => LeadScheduledEventStatus::Scheduled,
            'user_id' => $user?->id ?? auth()->id(),
        ]);

        $this->syncLeadScheduledAt($event, $scheduledAt);

        return $event;
    }

    public function complete(LeadScheduledEvent $event): LeadScheduledEvent
    {
        $event->update([
            'status' => LeadScheduledEventStatus::Completed,
            'completed_at' => now(),
        ]);

        $lead = $event->lead;

        $this->syncLeadScheduledField($lead, $event->type);

        if ($event->type === LeadScheduledEventType::FollowUp) {
            if ($event->sequence_number === 1 && $lead->status === LeadStatus::New) {
                $lead->update(['status' => LeadStatus::Contacted]);
            }

            if ($event->sequence_number === 2 && ! $lead->status->isClosed()) {
                $lead->update(['status' => LeadStatus::FollowUp]);
            }
        }

        if (
            $event->type === LeadScheduledEventType::SiteVisit
            && $event->sequence_number === 1
            && ! $lead->status->isClosed()
        ) {
            $lead->update(['status' => LeadStatus::SiteVisit]);
        }

        return $event->fresh();
    }

    public function completeLatest(Lead $lead, LeadScheduledEventType $type): ?LeadScheduledEvent
    {
        $event = LeadScheduledEvent::query()
            ->where('lead_id', $lead->id)
            ->where('type', $type)
            ->where('status', LeadScheduledEventStatus::Scheduled)
            ->orderByDesc('sequence_number')
            ->first();

        if ($event === null) {
            return null;
        }

        return $this->complete($event);
    }

    public function reschedule(
        LeadScheduledEvent $event,
        CarbonInterface $scheduledAt,
        ?string $notes = null,
        ?ScheduledActivityPriority $priority = null,
    ): LeadScheduledEvent {
        $updates = [
            'scheduled_at' => $scheduledAt,
            'rescheduled_at' => now(),
            'reminder_dismissed_at' => null,
        ];

        if ($notes !== null) {
            $updates['notes'] = $notes;
        }

        if ($priority !== null) {
            $updates['priority'] = $priority;
        }

        $event->update($updates);

        $this->syncLeadScheduledAt($event->fresh(), $scheduledAt);

        return $event->fresh();
    }

    public function syncLeadScheduledField(Lead $lead, LeadScheduledEventType $type): void
    {
        $latestScheduledAt = LeadScheduledEvent::query()
            ->where('lead_id', $lead->id)
            ->where('type', $type)
            ->where('status', LeadScheduledEventStatus::Scheduled)
            ->orderByDesc('sequence_number')
            ->value('scheduled_at');

        $leadField = match ($type) {
            LeadScheduledEventType::FollowUp => 'next_follow_up_at',
            LeadScheduledEventType::SiteVisit => 'upcoming_site_visit_at',
        };

        $lead->update([$leadField => $latestScheduledAt]);
    }

    private function syncLeadScheduledAt(LeadScheduledEvent $event, CarbonInterface $scheduledAt): void
    {
        $isLatestScheduled = LeadScheduledEvent::query()
            ->where('lead_id', $event->lead_id)
            ->where('type', $event->type)
            ->where('status', LeadScheduledEventStatus::Scheduled)
            ->orderByDesc('sequence_number')
            ->value('id') === $event->id;

        if (! $isLatestScheduled) {
            return;
        }

        $leadField = match ($event->type) {
            LeadScheduledEventType::FollowUp => 'next_follow_up_at',
            LeadScheduledEventType::SiteVisit => 'upcoming_site_visit_at',
        };

        $updates = [$leadField => $scheduledAt];

        if ($event->type === LeadScheduledEventType::SiteVisit && $event->lead->status === LeadStatus::New) {
            $updates['status'] = LeadStatus::SiteVisit;
        }

        if ($event->type === LeadScheduledEventType::FollowUp) {
            $updates['next_action'] = __('Follow-up');
        }

        if ($event->type === LeadScheduledEventType::SiteVisit) {
            $updates['next_action'] = __('Site Visit');
        }

        $event->lead()->update($updates);
    }
}
