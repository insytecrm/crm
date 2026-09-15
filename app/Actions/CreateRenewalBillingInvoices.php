<?php

namespace App\Actions;

use App\Enums\BillingCycle;
use App\Enums\BillingInvoiceStatus;
use App\Models\BillingInvoice;
use App\Models\PartnerSubscription;
use App\Support\Platform\QuotationPricing;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class CreateRenewalBillingInvoices
{
    /**
     * Create renewal invoices one day before the subscription billing date.
     */
    public function handle(?Carbon $onDate = null): int
    {
        $onDate ??= now();
        $renewalDate = $onDate->copy()->addDay()->startOfDay();
        $renewalDateEnd = $onDate->copy()->addDay()->endOfDay();
        $created = 0;

        PartnerSubscription::query()
            ->active()
            ->with(['tenant', 'plan'])
            ->whereNotNull('next_billing_at')
            ->whereBetween('next_billing_at', [$renewalDate, $renewalDateEnd])
            ->orderBy('id')
            ->each(function (PartnerSubscription $subscription) use (&$created, $renewalDate): void {
                if ($this->renewalInvoiceExists($subscription, $renewalDate)) {
                    return;
                }

                DB::transaction(function () use ($subscription, $renewalDate, &$created): void {
                    $planPrice = $this->planPrice($subscription);
                    $pricing = QuotationPricing::calculate($planPrice, 0);
                    $periodEnd = $subscription->billing_cycle === BillingCycle::Annual
                        ? $renewalDate->copy()->addYearNoOverflow()
                        : $renewalDate->copy()->addMonthNoOverflow();

                    $tenant = $subscription->tenant;
                    $ownerName = is_string($tenant?->owner_name) && $tenant->owner_name !== ''
                        ? $tenant->owner_name
                        : null;

                    BillingInvoice::query()->create([
                        'number' => BillingInvoice::nextNumber(),
                        'tenant_id' => $subscription->tenant_id,
                        'partner_subscription_id' => $subscription->id,
                        'plan_id' => $subscription->plan_id,
                        'billing_cycle' => $subscription->billing_cycle,
                        'period_start' => $renewalDate,
                        'period_end' => $periodEnd,
                        'subtotal' => $pricing['plan_price'],
                        'discount_amount' => $pricing['discount_amount'],
                        'tax_amount' => $pricing['tax_amount'],
                        'total' => $pricing['total'],
                        'status' => BillingInvoiceStatus::Pending,
                        'issued_at' => now(),
                        'due_at' => $renewalDate,
                        'billed_to_name' => $tenant?->name,
                        'billed_to_contact' => $ownerName,
                        'billed_to_email' => $tenant?->email,
                    ]);

                    $created++;
                });
            });

        return $created;
    }

    public function markOverdueInvoices(): int
    {
        return BillingInvoice::query()
            ->where('status', BillingInvoiceStatus::Pending)
            ->whereNotNull('due_at')
            ->where('due_at', '<', now())
            ->update([
                'status' => BillingInvoiceStatus::Overdue,
            ]);
    }

    private function renewalInvoiceExists(PartnerSubscription $subscription, Carbon $periodStart): bool
    {
        return BillingInvoice::query()
            ->where('partner_subscription_id', $subscription->id)
            ->whereDate('period_start', $periodStart->toDateString())
            ->whereIn('status', [
                BillingInvoiceStatus::Pending,
                BillingInvoiceStatus::Overdue,
                BillingInvoiceStatus::Paid,
            ])
            ->exists();
    }

    private function planPrice(PartnerSubscription $subscription): int
    {
        if ($subscription->amount > 0) {
            return (int) $subscription->amount;
        }

        return (int) ($subscription->billing_cycle === BillingCycle::Annual
            ? $subscription->plan?->price_annual
            : $subscription->plan?->price_monthly);
    }
}
