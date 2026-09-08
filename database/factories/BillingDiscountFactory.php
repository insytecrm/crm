<?php

namespace Database\Factories;

use App\Models\BillingDiscount;
use App\Models\BillingInvoice;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BillingDiscount>
 */
class BillingDiscountFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'billing_invoice_id' => null,
            'amount' => 2000,
            'reason' => 'Launch Offer',
            'applied_at' => now()->subDays(2),
            'applied_by' => User::factory()->superAdmin(),
        ];
    }

    public function forInvoice(BillingInvoice $invoice): static
    {
        return $this->state(fn (): array => [
            'tenant_id' => $invoice->tenant_id,
            'billing_invoice_id' => $invoice->id,
        ]);
    }
}
