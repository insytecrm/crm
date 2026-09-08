<?php

namespace App\Actions;

use App\Enums\SubscriptionStatus;
use App\Models\PartnerSubscription;

class PausePartnerSubscription
{
    public function handle(PartnerSubscription $subscription): PartnerSubscription
    {
        if ($subscription->status === SubscriptionStatus::Paused) {
            return $subscription;
        }

        $subscription->update([
            'status' => SubscriptionStatus::Paused,
            'paused_at' => now(),
        ]);

        return $subscription->refresh();
    }
}
