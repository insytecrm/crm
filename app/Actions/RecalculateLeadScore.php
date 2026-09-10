<?php

namespace App\Actions;

use App\Models\Lead;
use App\Support\LeadScoring;

class RecalculateLeadScore
{
    public function handle(Lead $lead): Lead
    {
        $lead->update(LeadScoring::calculate($lead));

        return $lead->fresh();
    }
}
