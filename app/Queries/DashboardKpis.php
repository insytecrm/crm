<?php

namespace App\Queries;

use App\Enums\LeadScheduledEventStatus;
use App\Enums\LeadScheduledEventType;
use App\Enums\LeadStatus;
use App\Models\Booking;
use App\Models\Lead;
use App\Models\LeadScheduledEvent;
use App\Support\RevenueFilter;
use Illuminate\Database\Eloquent\Builder;

class DashboardKpis
{
    public function __construct(
        private RevenueDashboard $revenueDashboard,
    ) {}

    /**
     * @return array{
     *     total_leads: int,
     *     active_leads: int,
     *     site_visits: int,
     *     bookings: int,
     *     revenue: int,
     * }
     */
    public function forTenant(): array
    {
        $closedStatuses = [LeadStatus::Converted, LeadStatus::Lost];

        return [
            'total_leads' => Lead::query()->count(),
            'active_leads' => Lead::query()
                ->whereNotIn('status', $closedStatuses)
                ->count(),
            'site_visits' => LeadScheduledEvent::query()
                ->where('type', LeadScheduledEventType::SiteVisit)
                ->where('status', LeadScheduledEventStatus::Scheduled)
                ->whereHas(
                    'lead',
                    fn (Builder $query): Builder => $query->whereNotIn('status', $closedStatuses),
                )
                ->count(),
            'bookings' => Booking::query()->count(),
            'revenue' => $this->revenueDashboard
                ->summary(new RevenueFilter)['total_revenue'],
        ];
    }
}
