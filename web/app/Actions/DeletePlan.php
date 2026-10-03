<?php

namespace App\Actions;

use App\Models\Plan;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DeletePlan
{
    public function handle(Plan $plan): void
    {
        if (! $plan->isArchived()) {
            throw ValidationException::withMessages([
                'plan' => __('Only archived plans can be deleted.'),
            ]);
        }

        $ongoingSubscriptions = $plan->ongoingSubscriptionsCount();

        if ($ongoingSubscriptions > 0) {
            throw ValidationException::withMessages([
                'plan' => trans_choice(
                    'Delete this plan only when no ongoing subscriptions remain. :count subscription is still active.|Delete this plan only when no ongoing subscriptions remain. :count subscriptions are still active.',
                    $ongoingSubscriptions,
                    ['count' => $ongoingSubscriptions],
                ),
            ]);
        }

        $quotationsCount = $plan->quotations()->count();

        if ($quotationsCount > 0) {
            throw ValidationException::withMessages([
                'plan' => trans_choice(
                    'This plan is linked to :count quotation and cannot be deleted.|This plan is linked to :count quotations and cannot be deleted.',
                    $quotationsCount,
                    ['count' => $quotationsCount],
                ),
            ]);
        }

        DB::transaction(function () use ($plan): void {
            $plan->partnerSubscriptions()->delete();
            $plan->delete();
        });
    }
}
