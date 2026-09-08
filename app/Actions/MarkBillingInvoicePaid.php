<?php

namespace App\Actions;

use App\Enums\BillingInvoiceStatus;
use App\Enums\BillingPaymentStatus;
use App\Enums\BillingPaymentType;
use App\Models\BillingInvoice;
use App\Models\BillingPayment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MarkBillingInvoicePaid
{
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

            return $invoice->refresh();
        });
    }
}
