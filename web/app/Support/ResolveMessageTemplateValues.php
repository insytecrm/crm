<?php

namespace App\Support;

use App\Enums\LeadLostReason;
use App\Enums\LeadScheduledEventStatus;
use App\Enums\LeadScheduledEventType;
use App\Models\Booking;
use App\Models\Lead;
use App\Models\LeadScheduledEvent;
use App\Models\Property;
use App\Models\Tenant;
use App\Models\User;
use App\Support\Microsite\MicrositeMoney;
use BackedEnum;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class ResolveMessageTemplateValues
{
    /**
     * @return array<string, mixed>
     */
    public function handle(Lead $lead, ?User $user = null): array
    {
        $lead->loadMissing([
            'assignedTo:id,name,email,phone',
            'latestBooking.property',
            'completedSiteVisitEvents.property',
            'scheduledEvents.property',
        ]);

        $user ??= auth()->user();
        $assigned = $lead->assignedTo;
        $company = tenant();
        $booking = $lead->latestBooking;
        $property = $this->propertyFor($lead, $booking);

        $followUpUpcoming = $this->upcomingEvent($lead, LeadScheduledEventType::FollowUp);
        $followUpCompleted = $this->latestCompletedEvent($lead, LeadScheduledEventType::FollowUp);
        $followUpPrevious = $this->previousCompletedEvent($lead, LeadScheduledEventType::FollowUp);

        $siteVisitUpcoming = $this->upcomingEvent($lead, LeadScheduledEventType::SiteVisit);
        $siteVisitCompleted = $this->latestCompletedEvent($lead, LeadScheduledEventType::SiteVisit);
        $siteVisitPrevious = $this->previousCompletedEvent($lead, LeadScheduledEventType::SiteVisit);

        return array_merge(
            [
                'lead.name' => $lead->name,
                'lead.phone' => $lead->phone,
                'lead.email' => $lead->email,
                'lead.status' => $this->label($lead->status),
                'lead.source' => $lead->sourceDisplay(),
                'lead.sub_source' => $lead->sub_source,
                'lead.budget' => $this->label($lead->budget),
                'lead.location' => $lead->location,
                'lead.property_type' => $this->label($lead->property_type),
                'lead.configuration' => $lead->configuration,
                'lead.next_action' => $lead->next_action,
                'lead.score' => $lead->lead_score,
                'lead.interested_in' => $lead->propertyInterestLabel(),
                'lead.created_at' => $this->date($lead->created_at),
                'lead.last_activity' => $lead->last_activity_at?->diffForHumans(),
                'lead.closed_at' => $this->dateTime($lead->closed_at),
                'lead.closing_reason' => $this->closingReason($lead),
                'lead.closing_notes' => $lead->closing_notes,
                'lead.next_follow_up' => $this->dateTime($lead->next_follow_up_at),
                'lead.next_follow_up_date' => $this->dateOnly($lead->next_follow_up_at),
                'lead.next_follow_up_time' => $this->timeOnly($lead->next_follow_up_at),
                'lead.upcoming_site_visit' => $this->dateTime($lead->upcoming_site_visit_at),
                'lead.upcoming_site_visit_date' => $this->dateOnly($lead->upcoming_site_visit_at),
                'lead.upcoming_site_visit_time' => $this->timeOnly($lead->upcoming_site_visit_at),
                'assigned.name' => $assigned?->name,
                'assigned.email' => $assigned?->email,
                'assigned.phone' => $assigned?->phone,
                'user.name' => $user?->name,
                'user.email' => $user?->email,
                'user.phone' => $user?->phone,
                'company.name' => $company instanceof Tenant ? $company->name : null,
                'company.email' => $company instanceof Tenant ? $company->email : null,
                'property.project_name' => $property?->project_name,
                'property.developer_name' => $property?->developer_name,
                'property.location' => $property?->project_location,
                'property.rera_number' => $property?->rera_number,
                'property.type' => $this->label($property?->property_type),
                'property.status' => $this->label($property?->project_status),
                'property.possession_date' => $this->date($property?->possession_date),
                'property.price_from' => MicrositeMoney::rupees($property?->price_from),
                'property.price_to' => MicrositeMoney::rupees($property?->price_to),
                'property.carpet_area' => $this->carpetArea($property),
                'property.sourcing_manager_name' => $property?->sourcing_manager_name,
                'property.sourcing_manager_contact' => $property?->sourcing_manager_contact,
                'property.amenities' => $this->amenities($property),
                'property.microsite_url' => $property?->micrositeUrl(),
                'booking.property' => $booking?->property?->listLabel(),
                'booking.unit_number' => $booking?->unit_number,
                'booking.configuration' => $booking?->configuration_name,
                'booking.agreement_value' => MicrositeMoney::rupees($booking?->agreement_value),
                'booking.booking_date' => $this->date($booking?->booking_date),
                'booking.agreement_date' => $this->date($booking?->agreement_date),
                'booking.invoice_number' => $booking?->invoice_number,
                'booking.invoice_date' => $this->date($booking?->invoice_date),
                'booking.payout_amount' => MicrositeMoney::rupees($booking?->payout_amount),
                'booking.payout_percent' => filled($booking?->payout_percent)
                    ? rtrim(rtrim(number_format((float) $booking->payout_percent, 2), '0'), '.').'%'
                    : null,
            ],
            $this->scheduledEventValues('follow_up.upcoming', $followUpUpcoming, true),
            $this->scheduledEventValues('follow_up.previous', $followUpPrevious, false),
            $this->scheduledEventValues('follow_up.completed', $followUpCompleted, false),
            $this->scheduledEventValues('site_visit.upcoming', $siteVisitUpcoming, true),
            $this->scheduledEventValues('site_visit.previous', $siteVisitPrevious, false),
            $this->scheduledEventValues('site_visit.completed', $siteVisitCompleted, false),
        );
    }

    private function propertyFor(Lead $lead, ?Booking $booking): ?Property
    {
        if ($booking?->property instanceof Property) {
            return $booking->property;
        }

        return $lead->completedSiteVisitEvents
            ->sortByDesc('completed_at')
            ->first()?->property;
    }

    private function upcomingEvent(Lead $lead, LeadScheduledEventType $type): ?LeadScheduledEvent
    {
        return $this->eventsOfType($lead, $type)
            ->where('status', LeadScheduledEventStatus::Scheduled)
            ->sortByDesc('sequence_number')
            ->first();
    }

    private function latestCompletedEvent(Lead $lead, LeadScheduledEventType $type): ?LeadScheduledEvent
    {
        return $this->completedEvents($lead, $type)->first();
    }

    private function previousCompletedEvent(Lead $lead, LeadScheduledEventType $type): ?LeadScheduledEvent
    {
        return $this->completedEvents($lead, $type)->skip(1)->first();
    }

    /**
     * @return Collection<int, LeadScheduledEvent>
     */
    private function completedEvents(Lead $lead, LeadScheduledEventType $type): Collection
    {
        return $this->eventsOfType($lead, $type)
            ->where('status', LeadScheduledEventStatus::Completed)
            ->sort(function (LeadScheduledEvent $first, LeadScheduledEvent $second): int {
                $firstCompletedAt = $first->completed_at?->timestamp ?? 0;
                $secondCompletedAt = $second->completed_at?->timestamp ?? 0;

                if ($firstCompletedAt !== $secondCompletedAt) {
                    return $secondCompletedAt <=> $firstCompletedAt;
                }

                return $second->sequence_number <=> $first->sequence_number;
            })
            ->values();
    }

    /**
     * @return Collection<int, LeadScheduledEvent>
     */
    private function eventsOfType(Lead $lead, LeadScheduledEventType $type): Collection
    {
        return $lead->scheduledEvents
            ->where('type', $type)
            ->values();
    }

    /**
     * @return array<string, mixed>
     */
    private function scheduledEventValues(string $prefix, ?LeadScheduledEvent $event, bool $upcoming): array
    {
        $when = $this->eventWhen($event, $upcoming);

        $values = [
            "{$prefix}.activity" => $event?->ordinalLabel(),
            "{$prefix}.date" => $this->dateOnly($when),
            "{$prefix}.time" => $this->timeOnly($when),
            "{$prefix}.priority" => $this->label($event?->priority),
            "{$prefix}.notes" => $event?->notes,
        ];

        if ($upcoming) {
            $values["{$prefix}.state"] = $this->upcomingState($event);

            if ($event?->type === LeadScheduledEventType::SiteVisit) {
                $values["{$prefix}.property"] = $event->property?->listLabel();
                $values["{$prefix}.visit_type"] = $this->label($event->visit_type);
            }

            return $values;
        }

        $values["{$prefix}.outcome"] = $this->label($event?->completion_outcome);
        $values["{$prefix}.next_step"] = $this->label($event?->next_step_type);

        if ($event?->type === LeadScheduledEventType::SiteVisit) {
            $values["{$prefix}.property"] = $event->property?->listLabel();
            $values["{$prefix}.visit_type"] = $this->label($event->visit_type);
            $values["{$prefix}.attended"] = $event->attendedLabel();
        } else {
            $values["{$prefix}.method"] = $this->label($event?->completion_method);
        }

        return $values;
    }

    private function upcomingState(?LeadScheduledEvent $event): ?string
    {
        if ($event === null || $event->status !== LeadScheduledEventStatus::Scheduled) {
            return null;
        }

        return $event->scheduled_at?->isPast()
            ? __('Overdue')
            : __('Scheduled');
    }

    private function eventWhen(?LeadScheduledEvent $event, bool $upcoming): mixed
    {
        if ($event === null) {
            return null;
        }

        if ($upcoming) {
            return $event->scheduled_at;
        }

        return $event->completed_at ?? $event->scheduled_at;
    }

    private function closingReason(Lead $lead): ?string
    {
        $reasons = collect($lead->lost_reasons ?? [])
            ->map(fn (mixed $reason): ?string => LeadLostReason::tryFrom((string) $reason)?->label())
            ->filter()
            ->values()
            ->implode(', ');

        if ($reasons !== '') {
            return $reasons;
        }

        return $this->label($lead->closing_reason);
    }

    private function label(mixed $value): ?string
    {
        if ($value instanceof BackedEnum) {
            return method_exists($value, 'label') ? $value->label() : $value->value;
        }

        return filled($value) ? (string) $value : null;
    }

    private function dateTime(mixed $value): ?string
    {
        $date = $this->carbon($value);

        return $date?->format('j M Y, g:i A');
    }

    private function dateOnly(mixed $value): ?string
    {
        $date = $this->carbon($value);

        return $date?->format('j M Y');
    }

    private function timeOnly(mixed $value): ?string
    {
        $date = $this->carbon($value);

        return $date?->format('g:i A');
    }

    private function date(mixed $value): ?string
    {
        if ($value instanceof Carbon) {
            return $value->format('j M Y');
        }

        return filled($value) ? (string) $value : null;
    }

    private function carbon(mixed $value): ?Carbon
    {
        if ($value instanceof Carbon) {
            return $value;
        }

        if (is_string($value) && $value !== '') {
            return Carbon::parse($value);
        }

        return null;
    }

    private function carpetArea(?Property $property): ?string
    {
        if ($property === null) {
            return null;
        }

        $from = $property->carpet_area_from_sqft;
        $to = $property->carpet_area_to_sqft;

        if ($from && $to && $from !== $to) {
            return number_format($from).'–'.number_format($to).' sq.ft';
        }

        $area = $from ?? $to;

        return $area ? number_format($area).' sq.ft' : null;
    }

    private function amenities(?Property $property): ?string
    {
        $amenities = collect($property?->amenities ?? [])
            ->filter(fn (mixed $value): bool => is_string($value) && $value !== '')
            ->values();

        return $amenities->isEmpty() ? null : $amenities->implode(', ');
    }
}
