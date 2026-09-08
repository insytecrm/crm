<?php

namespace Database\Factories;

use App\Enums\BillingCycle;
use App\Enums\QuotationStatus;
use App\Models\Plan;
use App\Models\Quotation;
use App\Support\Platform\QuotationPricing;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Quotation>
 */
class QuotationFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $planPrice = 4999;
        $pricing = QuotationPricing::calculate($planPrice, 0);

        return [
            'number' => 'QT-'.fake()->unique()->numerify('####'),
            'company_name' => fake()->company(),
            'owner_name' => fake()->name(),
            'email' => fake()->unique()->companyEmail(),
            'phone' => fake()->numerify('9#########'),
            'tenant_id' => null,
            'plan_id' => Plan::factory(),
            'billing_cycle' => BillingCycle::Monthly,
            'plan_price' => $pricing['plan_price'],
            'discount_amount' => $pricing['discount_amount'],
            'tax_amount' => $pricing['tax_amount'],
            'total' => $pricing['total'],
            'trial_enabled' => true,
            'trial_days' => 7,
            'valid_until' => now()->addDays(7)->toDateString(),
            'status' => QuotationStatus::Draft,
            'sent_at' => null,
            'viewed_at' => null,
            'accepted_at' => null,
            'rejected_at' => null,
            'expired_at' => null,
            'accepted_by_name' => null,
            'partner_subscription_id' => null,
            'onboarded_at' => null,
        ];
    }

    public function sent(): static
    {
        return $this->state(fn (): array => [
            'status' => QuotationStatus::Sent,
            'sent_at' => now()->subDay(),
        ]);
    }

    public function accepted(): static
    {
        return $this->state(fn (): array => [
            'status' => QuotationStatus::Accepted,
            'sent_at' => now()->subDays(2),
            'accepted_at' => now()->subDay(),
            'accepted_by_name' => fake()->name(),
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn (): array => [
            'status' => QuotationStatus::Rejected,
            'sent_at' => now()->subDays(2),
            'rejected_at' => now()->subDay(),
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn (): array => [
            'status' => QuotationStatus::Expired,
            'sent_at' => now()->subDays(10),
            'valid_until' => now()->subDays(3)->toDateString(),
            'expired_at' => now()->subDays(3),
        ]);
    }
}
