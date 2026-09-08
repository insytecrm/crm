<?php

namespace App\Actions;

use App\Enums\BillingInvoiceStatus;
use App\Models\BillingDiscount;
use App\Models\BillingInvoice;
use App\Models\PartnerSubscription;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ApplyPartnerBillingDiscount
{
    /**
     * @param  array{amount: int, reason: string}  $data
     */
    public function handle(PartnerSubscription $subscription, array $data): BillingDiscount
    {
        return DB::transaction(function () use ($subscription, $data): BillingDiscount {
            $invoice = BillingInvoice::query()
                ->where('partner_subscription_id', $subscription->id)
                ->whereIn('status', [BillingInvoiceStatus::Pending, BillingInvoiceStatus::Overdue])
                ->latest('issued_at')
                ->latest('id')
                ->first();

            if ($invoice !== null) {
                $discountAmount = min($data['amount'], max((int) $invoice->subtotal - (int) $invoice->discount_amount, 0));
                $newDiscountTotal = (int) $invoice->discount_amount + $discountAmount;

                $invoice->update([
                    'discount_amount' => $newDiscountTotal,
                    'total' => max((int) $invoice->subtotal - $newDiscountTotal + (int) $invoice->tax_amount, 0),
                ]);
            } else {
                $discountAmount = $data['amount'];
            }

            return BillingDiscount::query()->create([
                'tenant_id' => $subscription->tenant_id,
                'billing_invoice_id' => $invoice?->id,
                'amount' => $discountAmount,
                'reason' => $data['reason'],
                'applied_at' => now(),
                'applied_by' => Auth::id(),
            ]);
        });
    }
}
