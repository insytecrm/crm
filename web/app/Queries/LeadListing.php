<?php

namespace App\Queries;

use App\Enums\LeadListingFilter;
use App\Enums\LeadScheduledEventType;
use App\Models\Lead;
use App\Support\LeadListFilters;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class LeadListing
{
    public function paginate(LeadListingFilter $filter, string $search = '', ?LeadListFilters $listFilters = null): LengthAwarePaginator
    {
        $listFilters ??= new LeadListFilters;

        $query = Lead::query()
            ->with([
                'assignedTo',
                'completedSiteVisitEvents.property',
                'scheduledEvents.property',
                'latestBooking.property',
            ])
            ->withCount([
                'scheduledEvents as follow_ups_count' => fn ($query) => $query->where('type', LeadScheduledEventType::FollowUp),
                'scheduledEvents as site_visits_count' => fn ($query) => $query->where('type', LeadScheduledEventType::SiteVisit),
                'bookings',
            ])
            ->when($filter === LeadListingFilter::Priority, fn ($query) => $query->priority())
            ->when($filter === LeadListingFilter::New, fn ($query) => $query->newLeads())
            ->when($filter === LeadListingFilter::FollowUpDue, fn ($query) => $query->followUpDue())
            ->when($filter === LeadListingFilter::SiteVisitsScheduled, fn ($query) => $query->siteVisitsScheduled())
            ->when($filter === LeadListingFilter::Unassigned, fn ($query) => $query->unassigned())
            ->when($filter === LeadListingFilter::Converted, fn ($query) => $query->converted())
            ->when($filter === LeadListingFilter::Lost, fn ($query) => $query->lost());

        $listFilters->applyTo($query);

        $query->when($search !== '', function ($query) use ($search): void {
            $query->where(function ($query) use ($search): void {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        });

        if ($filter === LeadListingFilter::Priority) {
            $query
                ->orderByDesc('lead_score_intent')
                ->orderByDesc('latest_positive_outcome_at')
                ->orderByDesc('lead_score')
                ->orderByDesc('id');
        } else {
            $query->latest();
        }

        return $query
            ->paginate(15)
            ->withQueryString();
    }
}
