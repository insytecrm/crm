<?php

namespace App\Actions;

use App\Enums\BillingCycle;
use App\Enums\BillingInvoiceStatus;
use App\Models\BillingInvoice;
use App\Models\Quotation;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CreateInvoiceFromAcceptedQuotation
{
    public function handle(Quotation $quotation): BillingInvoice
    {
        if (! $quotation->isAccepted()) {
            throw ValidationException::withMessages([
                'quotation' => __('Only accepted quotations can generate an invoice.'),
            ]);
        }

        $existing = BillingInvoice::query()
            ->where('quotation_id', $quotation->id)
            ->where('status', '!=', BillingInvoiceStatus::Cancelled)
            ->first();

        if ($existing !== null) {
            return $existing;
        }

        return DB::transaction(function () use ($quotation): BillingInvoice {
            $quotation->loadMissing(['platformLead', 'plan']);
            $startedAt = now();
            $nextBillingAt = $quotation->billing_cycle === BillingCycle::Annual
                ? $startedAt->copy()->addYearNoOverflow()
                : $startedAt->copy()->addMonthNoOverflow();

            return BillingInvoice::query()->create([
                'number' => BillingInvoice::nextNumber(),
                'quotation_id' => $quotation->id,
                'tenant_id' => $quotation->platformLead?->tenant_id,
                'partner_subscription_id' => null,
                'plan_id' => $quotation->plan_id,
                'billing_cycle' => $quotation->billing_cycle,
                'period_start' => $startedAt,
                'period_end' => $nextBillingAt,
                'subtotal' => (int) $quotation->plan_price,
                'discount_amount' => (int) $quotation->discount_amount,
                'tax_amount' => (int) $quotation->tax_amount,
                'total' => (int) $quotation->total,
                'status' => BillingInvoiceStatus::Pending,
                'issued_at' => $startedAt,
                'due_at' => $startedAt->copy()->addDays(7),
                'billed_to_name' => $quotation->company_name,
                'billed_to_contact' => $quotation->owner_name,
                'billed_to_email' => $quotation->email,
            ]);
        });
    }
}
