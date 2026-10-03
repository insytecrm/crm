<?php

namespace App\Actions;

use App\Enums\SubscriptionStatus;
use App\Models\PartnerSubscription;

class CancelPartnerSubscription
{
    public function handle(PartnerSubscription $subscription): PartnerSubscription
    {
        $subscription->update([
            'status' => SubscriptionStatus::Cancelled,
            'cancelled_at' => now(),
            'next_billing_at' => null,
        ]);

        return $subscription->refresh();
    }
}
