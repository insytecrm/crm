<?php

namespace App\Actions;

use App\Enums\PlanStatus;
use App\Models\Plan;

class ArchivePlan
{
    public function handle(Plan $plan): Plan
    {
        $plan->update([
            'status' => PlanStatus::Archived,
        ]);

        return $plan->refresh();
    }
}
