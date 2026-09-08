<?php

namespace App\Actions;

use App\Enums\SubscriptionStatus;
use App\Models\PartnerSubscription;

class ExtendPartnerSubscriptionTrial
{
    public function handle(PartnerSubscription $subscription, int $days): PartnerSubscription
    {
        $base = $subscription->trial_ends_at && $subscription->trial_ends_at->isFuture()
            ? $subscription->trial_ends_at
            : now();

        $trialEndsAt = $base->copy()->addDays($days);

        $subscription->update([
            'status' => SubscriptionStatus::Trial,
            'trial_ends_at' => $trialEndsAt,
            'next_billing_at' => $trialEndsAt,
            'paused_at' => null,
            'cancelled_at' => null,
        ]);

        return $subscription->refresh();
    }
}
