<?php

namespace App\Actions;

use App\Enums\BillingCycle;
use App\Enums\BillingInvoiceStatus;
use App\Enums\BillingPaymentStatus;
use App\Enums\BillingPaymentType;
use App\Enums\SubscriptionStatus;
use App\Models\BillingInvoice;
use App\Models\BillingPayment;
use App\Models\PartnerSubscription;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MarkBillingInvoicePaid
{
    public function __construct(
        private SyncPlatformLeadFromQuotation $syncLead,
    ) {}

    public function handle(BillingInvoice $invoice): BillingInvoice
    {
        return DB::transaction(function () use ($invoice): BillingInvoice {
            $now = now();

            $invoice->update([
                'status' => BillingInvoiceStatus::Paid,
                'paid_at' => $now,
                'payment_initiated_at' => $invoice->payment_initiated_at ?? $now,
            ]);

            $payment = $invoice->payments()
                ->whereIn('status', [BillingPaymentStatus::Pending, BillingPaymentStatus::Failed])
                ->latest('id')
                ->first();

            if ($payment === null) {
                BillingPayment::query()->create([
                    'tenant_id' => $invoice->tenant_id,
                    'billing_invoice_id' => $invoice->id,
                    'partner_subscription_id' => $invoice->partner_subscription_id,
                    'plan_id' => $invoice->plan_id,
                    'amount' => $invoice->total,
                    'type' => BillingPaymentType::Subscription,
                    'status' => BillingPaymentStatus::Paid,
                    'payment_method' => 'Online',
                    'transaction_id' => 'MANUAL-'.Str::upper(Str::random(8)),
                    'payment_date' => $now,
                    'initiated_at' => $now,
                ]);
            } else {
                $payment->update([
                    'status' => BillingPaymentStatus::Paid,
                    'payment_date' => $now,
                    'failed_at' => null,
                    'transaction_id' => $payment->transaction_id ?: 'MANUAL-'.Str::upper(Str::random(8)),
                ]);
            }

            $this->advanceSubscriptionAfterPayment($invoice);
            $this->syncLeadAfterPayment($invoice->refresh());

            return $invoice->refresh();
        });
    }

    private function advanceSubscriptionAfterPayment(BillingInvoice $invoice): void
    {
        if ($invoice->partner_subscription_id === null || $invoice->period_end === null) {
            return;
        }

        /** @var PartnerSubscription|null $subscription */
        $subscription = PartnerSubscription::query()->find($invoice->partner_subscription_id);

        if ($subscription === null) {
            return;
        }

        $nextBillingAt = $invoice->billing_cycle === BillingCycle::Annual
            ? $invoice->period_end->copy()->addYearNoOverflow()
            : $invoice->period_end->copy()->addMonthNoOverflow();

        $subscription->update([
            'next_billing_at' => $nextBillingAt,
            'status' => $subscription->status === SubscriptionStatus::PastDue
                ? SubscriptionStatus::Active
                : $subscription->status,
        ]);
    }

    private function syncLeadAfterPayment(BillingInvoice $invoice): void
    {
        $invoice->loadMissing('quotation.platformLead');

        $lead = $invoice->quotation?->platformLead;

        if ($lead !== null) {
            $this->syncLead->afterInvoicePaid($lead);
        }
    }
}
