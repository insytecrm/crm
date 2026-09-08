<?php

namespace App\Actions;

use App\Enums\AutomationTrigger;
use App\Models\Automation;
use App\Models\Lead;
use Illuminate\Support\Collection;

class MatchLeadAutomations
{
    /**
     * @return Collection<int, Automation>
     */
    public function handle(Lead $lead, AutomationTrigger $trigger): Collection
    {
        if ($lead->assigned_to_id === null || $lead->isClosed()) {
            return collect();
        }

        return Automation::query()
            ->with(['conditions', 'actions'])
            ->where('user_id', $lead->assigned_to_id)
            ->active()
            ->where('trigger', $trigger)
            ->orderBy('id')
            ->get();
    }
}
