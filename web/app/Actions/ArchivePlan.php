<?php

namespace App\Actions;

use App\Enums\PlanStatus;
use App\Models\Plan;
use Illuminate\Validation\ValidationException;

class ArchivePlan
{
    public function handle(Plan $plan): Plan
    {
        if ($plan->isArchived()) {
            return $plan;
        }

        $ongoingSubscriptions = $plan->ongoingSubscriptionsCount();

        if ($ongoingSubscriptions > 0) {
            throw ValidationException::withMessages([
                'plan' => trans_choice(
                    'Archive this plan only when no ongoing subscriptions remain. :count subscription is still active.|Archive this plan only when no ongoing subscriptions remain. :count subscriptions are still active.',
                    $ongoingSubscriptions,
                    ['count' => $ongoingSubscriptions],
                ),
            ]);
        }

        $plan->update([
            'status' => PlanStatus::Archived,
        ]);

        return $plan->refresh();
    }
}
