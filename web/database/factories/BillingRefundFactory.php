<?php

namespace Database\Factories;

use App\Enums\BillingRefundStatus;
use App\Models\BillingPayment;
use App\Models\BillingRefund;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BillingRefund>
 */
class BillingRefundFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'billing_payment_id' => BillingPayment::factory(),
            'amount' => 4999,
            'reason' => 'Subscription cancellation',
            'status' => BillingRefundStatus::Completed,
            'refunded_at' => now()->subDay(),
        ];
    }

    public function forPayment(BillingPayment $payment): static
    {
        return $this->state(fn (): array => [
            'tenant_id' => $payment->tenant_id,
            'billing_payment_id' => $payment->id,
            'amount' => $payment->amount,
        ]);
    }
}
