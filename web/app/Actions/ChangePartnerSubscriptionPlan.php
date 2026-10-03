<?php

namespace App\Actions;

use App\Enums\BillingCycle;
use App\Models\PartnerSubscription;
use App\Models\Plan;
use App\Models\Tenant;
use Illuminate\Support\Facades\DB;

class ChangePartnerSubscriptionPlan
{
    public function handle(PartnerSubscription $subscription, Plan $plan, BillingCycle $cycle): PartnerSubscription
    {
        return DB::transaction(function () use ($subscription, $plan, $cycle): PartnerSubscription {
            $amount = $cycle === BillingCycle::Annual
                ? (int) $plan->price_annual
                : (int) $plan->price_monthly;

            $subscription->update([
                'plan_id' => $plan->id,
                'billing_cycle' => $cycle,
                'amount' => $amount,
                'currency' => $plan->currency ?: 'INR',
            ]);

            /** @var Tenant|null $tenant */
            $tenant = $subscription->tenant()->first();

            if ($tenant !== null) {
                $tenant->update([
                    'plan_key' => $plan->key,
                    'billing_cycle' => $cycle->value,
                ]);
            }

            return $subscription->refresh();
        });
    }
}
