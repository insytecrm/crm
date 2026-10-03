<?php

namespace App\Actions;

use App\Enums\QuotationStatus;
use App\Models\Quotation;
use App\Support\Platform\QuotationPricing;

class DuplicateQuotation
{
    public function handle(Quotation $quotation): Quotation
    {
        $pricing = QuotationPricing::calculate(
            (int) $quotation->plan_price,
            (int) $quotation->discount_amount,
            (int) $quotation->tax_amount,
        );

        return Quotation::query()->create([
            'number' => Quotation::nextNumber(),
            'company_name' => $quotation->company_name,
            'owner_name' => $quotation->owner_name,
            'email' => $quotation->email,
            'phone' => $quotation->phone,
            'tenant_id' => null,
            'plan_id' => $quotation->plan_id,
            'billing_cycle' => $quotation->billing_cycle,
            'plan_price' => $pricing['plan_price'],
            'discount_amount' => $pricing['discount_amount'],
            'tax_amount' => $pricing['tax_amount'],
            'total' => $pricing['total'],
            'trial_enabled' => $quotation->trial_enabled,
            'trial_days' => $quotation->trial_days,
            'valid_until' => now()->addDays(7)->toDateString(),
            'status' => QuotationStatus::Draft,
        ]);
    }
}
