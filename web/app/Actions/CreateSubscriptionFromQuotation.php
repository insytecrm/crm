<?php

namespace App\Actions;

use App\Enums\BillingCycle;
use App\Enums\BillingInvoiceStatus;
use App\Enums\QuotationStatus;
use App\Enums\SubscriptionStatus;
use App\Models\BillingInvoice;
use App\Models\PartnerSubscription;
use App\Models\Quotation;
use App\Models\Tenant;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CreateSubscriptionFromQuotation
{
    public function handle(Quotation $quotation): PartnerSubscription
    {
        if (! $quotation->canCreateSubscription()) {
            throw ValidationException::withMessages([
                'quotation' => __('Only accepted quotations without a subscription can create one.'),
            ]);
        }

        return DB::transaction(function () use ($quotation): PartnerSubscription {
            $quotation->loadMissing(['tenant', 'plan']);

            $amount = max((int) $quotation->plan_price - (int) $quotation->discount_amount, 0);
            $trialEnabled = $quotation->trial_enabled && (int) $quotation->trial_days > 0;
            $startedAt = now();
            $trialEndsAt = $trialEnabled ? $startedAt->copy()->addDays((int) $quotation->trial_days) : null;
            $nextBillingAt = $trialEndsAt
                ?? ($quotation->billing_cycle === BillingCycle::Annual
                    ? $startedAt->copy()->addYearNoOverflow()
                    : $startedAt->copy()->addMonthNoOverflow());

            $subscription = PartnerSubscription::query()->create([
                'tenant_id' => $quotation->tenant_id,
                'plan_id' => $quotation->plan_id,
                'billing_cycle' => $quotation->billing_cycle,
                'amount' => $amount,
                'currency' => $quotation->plan?->currency ?: 'INR',
                'status' => $trialEnabled ? SubscriptionStatus::Trial : SubscriptionStatus::Active,
                'started_at' => $startedAt,
                'trial_ends_at' => $trialEndsAt,
                'next_billing_at' => $nextBillingAt,
            ]);

            /** @var Tenant|null $tenant */
            $tenant = $quotation->tenant;

            if ($tenant !== null) {
                $tenant->update([
                    'plan_key' => $quotation->plan?->key,
                    'billing_cycle' => $quotation->billing_cycle?->value,
                ]);
            }

            $ownerName = is_string($tenant?->owner_name) && $tenant->owner_name !== ''
                ? $tenant->owner_name
                : null;

            BillingInvoice::query()->create([
                'number' => $this->nextInvoiceNumber(),
                'tenant_id' => $quotation->tenant_id,
                'partner_subscription_id' => $subscription->id,
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
                'due_at' => $nextBillingAt,
                'billed_to_name' => $tenant?->name,
                'billed_to_contact' => $ownerName,
                'billed_to_email' => $tenant?->email,
            ]);

            $quotation->update([
                'partner_subscription_id' => $subscription->id,
                'status' => QuotationStatus::Accepted,
            ]);

            return $subscription->refresh();
        });
    }

    private function nextInvoiceNumber(): string
    {
        $latest = BillingInvoice::query()->orderByDesc('id')->value('number');
        $sequence = 1001;

        if (is_string($latest) && preg_match('/^INV-(\d+)$/', $latest, $matches) === 1) {
            $sequence = ((int) $matches[1]) + 1;
        }

        return 'INV-'.$sequence;
    }
}
