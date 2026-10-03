<?php

namespace Database\Factories;

use App\Enums\BillingCycle;
use App\Enums\BillingInvoiceStatus;
use App\Models\BillingInvoice;
use App\Models\PartnerSubscription;
use App\Models\Plan;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BillingInvoice>
 */
class BillingInvoiceFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $issuedAt = now()->subDays(5);
        $amount = 4999;

        return [
            'number' => 'INV-'.fake()->unique()->numerify('####'),
            'tenant_id' => Tenant::factory(),
            'partner_subscription_id' => null,
            'plan_id' => Plan::factory(),
            'billing_cycle' => BillingCycle::Monthly,
            'period_start' => $issuedAt->copy(),
            'period_end' => $issuedAt->copy()->addMonthNoOverflow(),
            'subtotal' => $amount,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'total' => $amount,
            'status' => BillingInvoiceStatus::Paid,
            'issued_at' => $issuedAt,
            'due_at' => $issuedAt->copy()->addMonthNoOverflow(),
            'billed_to_name' => fake()->company(),
            'billed_to_contact' => fake()->name(),
            'billed_to_email' => fake()->safeEmail(),
            'sent_at' => $issuedAt,
            'paid_at' => $issuedAt,
            'payment_initiated_at' => $issuedAt,
        ];
    }

    public function forSubscription(PartnerSubscription $subscription): static
    {
        return $this->state(fn (): array => [
            'tenant_id' => $subscription->tenant_id,
            'partner_subscription_id' => $subscription->id,
            'plan_id' => $subscription->plan_id,
            'billing_cycle' => $subscription->billing_cycle,
            'subtotal' => $subscription->amount,
            'total' => $subscription->amount,
            'billed_to_name' => $subscription->tenant?->name,
        ]);
    }

    public function pending(): static
    {
        return $this->state(fn (): array => [
            'status' => BillingInvoiceStatus::Pending,
            'paid_at' => null,
        ]);
    }

    public function overdue(): static
    {
        return $this->state(fn (): array => [
            'status' => BillingInvoiceStatus::Overdue,
            'paid_at' => null,
            'due_at' => now()->subDays(5),
        ]);
    }
}
