<?php

namespace App\Queries;

use App\Enums\LeadStatus;
use App\Models\Lead;

class LeadStatistics
{
    /**
     * @return array{
     *     total: int,
     *     new: int,
     *     follow_up_due: int,
     *     site_visits_scheduled: int,
     *     unassigned: int,
     *     converted: int,
     *     lost: int,
     * }
     */
    public function forTenant(): array
    {
        return [
            'total' => Lead::query()->count(),
            'new' => Lead::query()->where('status', LeadStatus::New)->count(),
            'follow_up_due' => Lead::query()
                ->whereNotNull('next_follow_up_at')
                ->where('next_follow_up_at', '<=', now())
                ->whereNotIn('status', [LeadStatus::Converted, LeadStatus::Lost])
                ->count(),
            'site_visits_scheduled' => Lead::query()
                ->whereNotNull('upcoming_site_visit_at')
                ->where('upcoming_site_visit_at', '>', now())
                ->count(),
            'unassigned' => Lead::query()->whereNull('assigned_to_id')->count(),
            'converted' => Lead::query()->where('status', LeadStatus::Converted)->count(),
            'lost' => Lead::query()->where('status', LeadStatus::Lost)->count(),
        ];
    }
}
