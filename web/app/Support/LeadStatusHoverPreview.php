<?php

namespace App\Support;

use App\Enums\LeadLostReason;
use App\Enums\LeadScheduledEventStatus;
use App\Enums\LeadScheduledEventType;
use App\Enums\LeadStatus;
use App\Models\Booking;
use App\Models\Lead;
use App\Models\LeadScheduledEvent;
use Illuminate\Support\Collection;

class LeadStatusHoverPreview
{
    /**
     * @return array{
     *     status: string,
     *     stage: ?string,
     *     fields: list<array{label: string, value: string|null, wide?: bool}>
     * }
     */
    public static function for(Lead $lead): array
    {
        return [
            'status' => $lead->status->label(),
            'stage' => $lead->statusStageLabel(),
            'fields' => self::fields($lead),
        ];
    }

    /**
     * @return list<array{label: string, value: string|null, wide?: bool}>
     */
    private static function fields(Lead $lead): array
    {
        return match ($lead->status) {
            LeadStatus::New => self::newFields($lead),
            LeadStatus::Contacted => self::contactedFields($lead),
            LeadStatus::Qualified => self::qualifiedFields($lead),
            LeadStatus::FollowUp => self::followUpFields($lead),
            LeadStatus::SiteVisit => self::siteVisitFields($lead),
            LeadStatus::Negotiation => self::negotiationFields($lead),
            LeadStatus::Converted => self::convertedFields($lead),
            LeadStatus::Lost => self::lostFields($lead),
        };
    }

    /**
     * @return list<array{label: string, value: string|null, wide?: bool}>
     */
    private static function newFields(Lead $lead): array
    {
        return [
            self::field(__('Created'), $lead->created_at?->format('M j, Y')),
            self::field(__('Source'), $lead->sourceDisplay()),
            self::field(__('Assigned To'), $lead->assignedTo?->name),
            self::field(__('Budget'), $lead->budget?->label()),
            self::field(__('Location'), $lead->location),
            self::field(__('Property Type'), $lead->property_type?->label()),
            self::field(__('Next action'), $lead->next_action),
        ];
    }

    /**
     * @return list<array{label: string, value: string|null, wide?: bool}>
     */
    private static function contactedFields(Lead $lead): array
    {
        $followUp = self::currentEvent($lead, LeadScheduledEventType::FollowUp);

        return [
            self::field(__('Next Follow-up'), self::eventWhen($followUp) ?? $lead->next_follow_up_at?->format('M j, Y g:i A')),
            self::field(__('Activity'), $followUp?->ordinalLabel()),
            self::field(__('Priority'), $followUp?->priority?->label()),
            self::field(__('Assigned To'), $lead->assignedTo?->name),
            self::field(__('Last activity'), $lead->last_activity_at?->diffForHumans()),
            self::field(__('Source'), $lead->sourceDisplay()),
        ];
    }

    /**
     * @return list<array{label: string, value: string|null, wide?: bool}>
     */
    private static function qualifiedFields(Lead $lead): array
    {
        $nextEvent = self::currentEvent($lead, LeadScheduledEventType::SiteVisit)
            ?? self::currentEvent($lead, LeadScheduledEventType::FollowUp);

        return [
            self::field(__('Budget'), $lead->budget?->label()),
            self::field(__('Property Type'), $lead->property_type?->label()),
            self::field(__('Configuration'), $lead->configuration),
            self::field(__('Location'), $lead->location),
            self::field(__('Next activity'), $nextEvent?->ordinalLabel()),
            self::field(__('When'), self::eventWhen($nextEvent)),
        ];
    }

    /**
     * @return list<array{label: string, value: string|null, wide?: bool}>
     */
    private static function followUpFields(Lead $lead): array
    {
        $event = self::currentEvent($lead, LeadScheduledEventType::FollowUp);

        $fields = [
            self::field(__('Activity'), $event?->ordinalLabel()),
            self::field(__('State'), self::eventState($event)),
            self::field(__('Date & Time'), self::eventWhen($event) ?? $lead->next_follow_up_at?->format('M j, Y g:i A')),
            self::field(__('Priority'), $event?->priority?->label()),
            self::field(__('Notes'), $event?->notes, wide: true),
        ];

        if ($event?->status === LeadScheduledEventStatus::Completed) {
            $fields[] = self::field(__('Method'), $event->completion_method?->label());
            $fields[] = self::field(__('Outcome'), $event->completion_outcome?->label());
            $fields[] = self::field(__('Next step'), $event->next_step_type?->label());
        }

        return $fields;
    }

    /**
     * @return list<array{label: string, value: string|null, wide?: bool}>
     */
    private static function siteVisitFields(Lead $lead): array
    {
        $event = self::currentEvent($lead, LeadScheduledEventType::SiteVisit);

        $fields = [
            self::field(__('Activity'), $event?->ordinalLabel()),
            self::field(__('State'), self::eventState($event)),
            self::field(__('Date & Time'), self::eventWhen($event) ?? $lead->upcoming_site_visit_at?->format('M j, Y g:i A')),
            self::field(__('Property'), $event?->property?->listLabel(), wide: true),
            self::field(__('Visit type'), $event?->visit_type?->label()),
            self::field(__('Priority'), $event?->priority?->label()),
        ];

        if ($event?->status === LeadScheduledEventStatus::Completed) {
            $fields[] = self::field(__('Attended'), $event->attendedLabel());
            $fields[] = self::field(__('Outcome'), $event->completion_outcome?->label());
            $fields[] = self::field(__('Next step'), $event->next_step_type?->label());
        }

        return $fields;
    }

    /**
     * @return list<array{label: string, value: string|null, wide?: bool}>
     */
    private static function negotiationFields(Lead $lead): array
    {
        $lastVisit = self::currentEvent($lead, LeadScheduledEventType::SiteVisit);

        return [
            self::field(__('Next action'), $lead->next_action ?: __('In negotiation')),
            self::field(__('Interested in'), $lead->propertyInterestLabel(), wide: true),
            self::field(__('Last visit'), $lastVisit?->ordinalLabel()),
            self::field(__('Attended'), $lastVisit?->attendedLabel()),
            self::field(__('Outcome'), $lastVisit?->completion_outcome?->label()),
            self::field(__('Assigned To'), $lead->assignedTo?->name),
        ];
    }

    /**
     * @return list<array{label: string, value: string|null, wide?: bool}>
     */
    private static function convertedFields(Lead $lead): array
    {
        $booking = self::booking($lead);

        if ($booking === null) {
            return [
                self::field(__('Closed at'), $lead->closed_at?->format('M j, Y g:i A')),
                self::field(__('Assigned To'), $lead->assignedTo?->name),
            ];
        }

        return [
            self::field(__('Property'), $booking->property?->listLabel(), wide: true),
            self::field(__('Unit'), $booking->unit_number),
            self::field(__('Configuration'), $booking->configuration_name),
            self::field(__('Booking date'), $booking->booking_date?->format('M j, Y')),
            self::field(__('Agreement value'), $booking->agreement_value !== null
                ? '₹'.number_format((int) $booking->agreement_value)
                : null),
            self::field(__('Agreement'), $booking->agreement_date?->format('M j, Y')),
            self::field(__('Invoice'), $booking->invoice_number
                ?? ($booking->hasInvoice() ? __('Created') : null)),
            self::field(__('Payout'), $booking->payout_paid_at?->format('M j, Y')
                ?? ($booking->hasPaidPayout() ? __('Paid') : null)),
        ];
    }

    /**
     * @return list<array{label: string, value: string|null, wide?: bool}>
     */
    private static function lostFields(Lead $lead): array
    {
        $reasons = collect($lead->lost_reasons ?? [])
            ->map(fn (mixed $reason): ?string => LeadLostReason::tryFrom((string) $reason)?->label())
            ->filter()
            ->values()
            ->implode(', ');

        return [
            self::field(__('Reasons'), $reasons !== '' ? $reasons : $lead->closing_reason?->label(), wide: true),
            self::field(__('Notes'), $lead->closing_notes, wide: true),
            self::field(__('Closed at'), $lead->closed_at?->format('M j, Y g:i A')),
            self::field(__('Assigned To'), $lead->assignedTo?->name),
            self::field(__('Last activity'), $lead->last_activity_at?->diffForHumans()),
        ];
    }

    /**
     * @return array{label: string, value: string|null, wide?: bool}
     */
    private static function field(string $label, ?string $value, bool $wide = false): array
    {
        $field = [
            'label' => $label,
            'value' => filled($value) ? $value : null,
        ];

        if ($wide) {
            $field['wide'] = true;
        }

        return $field;
    }

    private static function currentEvent(Lead $lead, LeadScheduledEventType $type): ?LeadScheduledEvent
    {
        $events = self::eventsOfType($lead, $type);

        $scheduled = $events
            ->where('status', LeadScheduledEventStatus::Scheduled)
            ->sortByDesc('sequence_number')
            ->first();

        if ($scheduled instanceof LeadScheduledEvent) {
            return $scheduled;
        }

        $completed = $events
            ->where('status', LeadScheduledEventStatus::Completed)
            ->sortByDesc('sequence_number')
            ->first();

        return $completed instanceof LeadScheduledEvent ? $completed : null;
    }

    /**
     * @return Collection<int, LeadScheduledEvent>
     */
    private static function eventsOfType(Lead $lead, LeadScheduledEventType $type): Collection
    {
        $events = $lead->relationLoaded('scheduledEvents')
            ? $lead->scheduledEvents
            : $lead->scheduledEvents()->with('property')->get();

        return $events->where('type', $type)->values();
    }

    private static function booking(Lead $lead): ?Booking
    {
        if ($lead->relationLoaded('latestBooking')) {
            return $lead->latestBooking;
        }

        return $lead->latestBooking()->with('property')->first();
    }

    private static function eventState(?LeadScheduledEvent $event): ?string
    {
        if ($event === null) {
            return null;
        }

        return match ($event->status) {
            LeadScheduledEventStatus::Scheduled => $event->scheduled_at?->isPast()
                ? __('Overdue')
                : __('Scheduled'),
            LeadScheduledEventStatus::Completed => __('Completed'),
        };
    }

    private static function eventWhen(?LeadScheduledEvent $event): ?string
    {
        if ($event === null) {
            return null;
        }

        if ($event->status === LeadScheduledEventStatus::Completed) {
            return $event->completed_at?->format('M j, Y g:i A')
                ?? $event->scheduled_at?->format('M j, Y g:i A');
        }

        return $event->scheduled_at?->format('M j, Y g:i A');
    }
}
