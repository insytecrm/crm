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
        $now = now();
        $new = LeadStatus::New->value;
        $converted = LeadStatus::Converted->value;
        $lost = LeadStatus::Lost->value;

        $row = Lead::query()
            ->toBase()
            ->selectRaw(
                'count(*) as total,
                 coalesce(sum(case when status = ? then 1 else 0 end), 0) as new_count,
                 coalesce(sum(case when next_follow_up_at is not null and next_follow_up_at <= ? and status not in (?, ?) then 1 else 0 end), 0) as follow_up_due,
                 coalesce(sum(case when upcoming_site_visit_at is not null and upcoming_site_visit_at > ? then 1 else 0 end), 0) as site_visits_scheduled,
                 coalesce(sum(case when assigned_to_id is null then 1 else 0 end), 0) as unassigned,
                 coalesce(sum(case when status = ? then 1 else 0 end), 0) as converted,
                 coalesce(sum(case when status = ? then 1 else 0 end), 0) as lost',
                [$new, $now, $converted, $lost, $now, $converted, $lost],
            )
            ->first();

        return [
            'total' => (int) ($row->total ?? 0),
            'new' => (int) ($row->new_count ?? 0),
            'follow_up_due' => (int) ($row->follow_up_due ?? 0),
            'site_visits_scheduled' => (int) ($row->site_visits_scheduled ?? 0),
            'unassigned' => (int) ($row->unassigned ?? 0),
            'converted' => (int) ($row->converted ?? 0),
            'lost' => (int) ($row->lost ?? 0),
        ];
    }
}
