<?php

namespace App\Support;

use App\Models\Lead;
use Illuminate\Support\Collection;

class DuplicateLeadPrimaryRecommendation
{
    /**
     * @param  Collection<int, Lead>  $leads
     */
    public function forGroup(Collection $leads): int
    {
        if ($leads->isEmpty()) {
            throw new \InvalidArgumentException('Duplicate group must contain at least one lead.');
        }

        return $leads
            ->sort(function (Lead $first, Lead $second): int {
                $activityCompare = ((int) ($second->activities_count ?? 0)) <=> ((int) ($first->activities_count ?? 0));

                if ($activityCompare !== 0) {
                    return $activityCompare;
                }

                return ($first->created_at?->getTimestamp() ?? 0) <=> ($second->created_at?->getTimestamp() ?? 0);
            })
            ->first()
            ->id;
    }
}
