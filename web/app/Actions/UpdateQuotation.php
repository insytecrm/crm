<?php

namespace App\Actions;

use App\Enums\BillingCycle;
use App\Models\Plan;
use App\Models\Quotation;
use App\Support\Platform\QuotationPricing;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class UpdateQuotation
{
    /**
     * @param  array{
     *     company_name: string,
     *     owner_name: string,
     *     email: string,
     *     phone?: string|null,
     *     plan_id: int,
     *     billing_cycle: string,
     *     plan_price?: int,
     *     discount_amount?: int,
     *     tax_amount?: int,
     *     trial_enabled?: bool,
     *     trial_days?: int|null,
     *     valid_until: string|\DateTimeInterface
     * }  $data
     */
    public function handle(Quotation $quotation, array $data): Quotation
    {
        if (! $quotation->canEdit()) {
            throw ValidationException::withMessages([
                'quotation' => __('Only draft quotations can be edited. Duplicate to create a new quotation.'),
            ]);
        }

        $plan = Plan::query()->findOrFail($data['plan_id']);
        $cycle = BillingCycle::from($data['billing_cycle']);

        $planPrice = array_key_exists('plan_price', $data)
            ? (int) $data['plan_price']
            : ($cycle === BillingCycle::Annual ? (int) $plan->price_annual : (int) $plan->price_monthly);

        $discount = (int) ($data['discount_amount'] ?? 0);
        $taxProvided = array_key_exists('tax_amount', $data) ? (int) $data['tax_amount'] : null;
        $pricing = QuotationPricing::calculate($planPrice, $discount, $taxProvided);

        $trialEnabled = (bool) ($data['trial_enabled'] ?? false);

        $quotation->update([
            'company_name' => $data['company_name'],
            'owner_name' => $data['owner_name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'plan_id' => $plan->id,
            'billing_cycle' => $cycle,
            'plan_price' => $pricing['plan_price'],
            'discount_amount' => $pricing['discount_amount'],
            'tax_amount' => $pricing['tax_amount'],
            'total' => $pricing['total'],
            'trial_enabled' => $trialEnabled,
            'trial_days' => $trialEnabled ? (int) ($data['trial_days'] ?? 7) : null,
            'valid_until' => Carbon::parse($data['valid_until'])->toDateString(),
        ]);

        return $quotation->refresh();
    }
}
