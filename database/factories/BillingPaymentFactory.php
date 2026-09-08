<?php

namespace Database\Factories;

use App\Enums\BillingPaymentStatus;
use App\Enums\BillingPaymentType;
use App\Models\BillingInvoice;
use App\Models\BillingPayment;
use App\Models\Plan;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BillingPayment>
 */
class BillingPaymentFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $paidAt = now()->subDay();

        return [
            'tenant_id' => Tenant::factory(),
            'billing_invoice_id' => null,
            'partner_subscription_id' => null,
            'plan_id' => Plan::factory(),
            'amount' => 4999,
            'type' => BillingPaymentType::Subscription,
            'status' => BillingPaymentStatus::Paid,
            'payment_method' => 'Online',
            'transaction_id' => 'TXN-'.fake()->unique()->numerify('######'),
            'payment_date' => $paidAt,
            'initiated_at' => $paidAt,
            'failed_at' => null,
            'retried_at' => null,
            'reminder_sent_at' => null,
            'gateway' => null,
            'gateway_transaction_id' => null,
            'response_code' => null,
            'failure_reason' => null,
            'webhook_status' => null,
        ];
    }

    public function forInvoice(BillingInvoice $invoice): static
    {
        return $this->state(fn (): array => [
            'tenant_id' => $invoice->tenant_id,
            'billing_invoice_id' => $invoice->id,
            'partner_subscription_id' => $invoice->partner_subscription_id,
            'plan_id' => $invoice->plan_id,
            'amount' => $invoice->total,
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn (): array => [
            'status' => BillingPaymentStatus::Failed,
            'payment_date' => now()->subDay(),
            'failed_at' => now()->subDay(),
            'failure_reason' => 'Card declined',
            'gateway' => 'razorpay',
            'gateway_transaction_id' => 'gw_'.fake()->numerify('######'),
            'response_code' => 'DECLINED',
            'webhook_status' => 'received',
        ]);
    }

    public function pending(): static
    {
        return $this->state(fn (): array => [
            'status' => BillingPaymentStatus::Pending,
            'payment_date' => now(),
            'transaction_id' => null,
        ]);
    }
}
