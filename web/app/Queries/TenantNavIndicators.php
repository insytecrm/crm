<?php

namespace App\Queries;

use App\Enums\LeadScheduledEventStatus;
use App\Enums\LeadStatus;
use App\Models\Lead;
use App\Models\LeadScheduledEvent;

class TenantNavIndicators
{
    /**
     * @return array{
     *     unassigned_leads: int,
     *     overdue_activities: int,
     * }
     */
    public function forTenant(): array
    {
        return [
            'unassigned_leads' => $this->unassignedLeadsCount(),
            'overdue_activities' => $this->overdueActivitiesCount(),
        ];
    }

    public function unassignedLeadsCount(): int
    {
        return Lead::query()
            ->whereNull('assigned_to_id')
            ->whereNotIn('status', [LeadStatus::Converted, LeadStatus::Lost])
            ->count();
    }

    public function overdueActivitiesCount(): int
    {
        return LeadScheduledEvent::query()
            ->where('status', LeadScheduledEventStatus::Scheduled)
            ->where('scheduled_at', '<', now())
            ->count();
    }
}
