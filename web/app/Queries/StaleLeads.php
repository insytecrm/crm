<?php

namespace App\Queries;

use App\Enums\LeadStatus;
use App\Models\Lead;
use Illuminate\Support\Collection;

class StaleLeads
{
    /**
     * @return Collection<int, Lead>
     */
    public function forTenant(?int $days = null, int $limit = 15): Collection
    {
        $days ??= (int) config('crm_workflow.stale_lead_days', 7);
        $cutoff = now()->subDays(max($days, 1));

        return Lead::query()
            ->with('assignedTo')
            ->whereNotIn('status', [LeadStatus::Converted, LeadStatus::Lost])
            ->where(function ($query) use ($cutoff): void {
                $query
                    ->where('last_activity_at', '<', $cutoff)
                    ->orWhere(function ($query) use ($cutoff): void {
                        $query
                            ->whereNull('last_activity_at')
                            ->where('created_at', '<', $cutoff);
                    });
            })
            ->orderByRaw('last_activity_at is null desc')
            ->orderBy('last_activity_at')
            ->limit($limit)
            ->get();
    }
}
