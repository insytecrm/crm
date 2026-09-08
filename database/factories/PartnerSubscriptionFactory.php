<?php

namespace Database\Factories;

use App\Enums\BillingCycle;
use App\Enums\SubscriptionStatus;
use App\Models\PartnerSubscription;
use App\Models\Plan;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PartnerSubscription>
 */
class PartnerSubscriptionFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startedAt = now()->subMonths(2);

        return [
            'tenant_id' => Tenant::factory(),
            'plan_id' => Plan::factory(),
            'billing_cycle' => BillingCycle::Monthly,
            'amount' => 4999,
            'currency' => 'INR',
            'status' => SubscriptionStatus::Active,
            'started_at' => $startedAt,
            'trial_ends_at' => null,
            'next_billing_at' => now()->addDays(15),
            'paused_at' => null,
            'cancelled_at' => null,
        ];
    }

    public function trial(): static
    {
        return $this->state(fn (): array => [
            'status' => SubscriptionStatus::Trial,
            'trial_ends_at' => now()->addDays(5),
            'next_billing_at' => now()->addDays(5),
        ]);
    }

    public function pastDue(): static
    {
        return $this->state(fn (): array => [
            'status' => SubscriptionStatus::PastDue,
            'next_billing_at' => now()->subDays(3),
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (): array => [
            'status' => SubscriptionStatus::Cancelled,
            'cancelled_at' => now()->subDay(),
            'next_billing_at' => null,
        ]);
    }

    public function paused(): static
    {
        return $this->state(fn (): array => [
            'status' => SubscriptionStatus::Paused,
            'paused_at' => now(),
        ]);
    }
}
