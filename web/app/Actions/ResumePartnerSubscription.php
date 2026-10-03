<?php

namespace App\Actions;

use App\Enums\SubscriptionStatus;
use App\Models\PartnerSubscription;

class ResumePartnerSubscription
{
    public function handle(PartnerSubscription $subscription): PartnerSubscription
    {
        if ($subscription->status !== SubscriptionStatus::Paused) {
            return $subscription;
        }

        $status = $subscription->trial_ends_at?->isFuture()
            ? SubscriptionStatus::Trial
            : SubscriptionStatus::Active;

        $subscription->update([
            'status' => $status,
            'paused_at' => null,
        ]);

        return $subscription->refresh();
    }
}
