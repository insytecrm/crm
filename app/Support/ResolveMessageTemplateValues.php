<?php

namespace App\Support;

use App\Models\Booking;
use App\Models\Lead;
use App\Models\Property;
use App\Models\Tenant;
use App\Models\User;
use App\Support\Microsite\MicrositeMoney;
use BackedEnum;
use Illuminate\Support\Carbon;

class ResolveMessageTemplateValues
{
    /**
     * @return array<string, mixed>
     */
    public function handle(Lead $lead, ?User $user = null): array
    {
        $lead->loadMissing([
            'assignedTo:id,name,email',
            'latestBooking.property',
            'completedSiteVisitEvents.property',
        ]);

        $user ??= auth()->user();
        $assigned = $lead->assignedTo;
        $company = tenant();
        $booking = $lead->latestBooking;
        $property = $this->propertyFor($lead, $booking);

        return [
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
            'lead.next_follow_up' => $this->dateTime($lead->next_follow_up_at),
            'lead.upcoming_site_visit' => $this->dateTime($lead->upcoming_site_visit_at),
            'assigned.name' => $assigned?->name,
            'assigned.email' => $assigned?->email,
            'user.name' => $user?->name,
            'user.email' => $user?->email,
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
            'booking.unit_number' => $booking?->unit_number,
            'booking.configuration' => $booking?->configuration_name,
            'booking.agreement_value' => MicrositeMoney::rupees($booking?->agreement_value),
            'booking.booking_date' => $this->date($booking?->booking_date),
            'booking.agreement_date' => $this->date($booking?->agreement_date),
            'booking.invoice_number' => $booking?->invoice_number,
        ];
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
