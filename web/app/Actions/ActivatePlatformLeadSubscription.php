<?php

namespace App\Actions;

use App\Enums\BillingCycle;
use App\Enums\PlatformLeadActivityType;
use App\Enums\PlatformLeadStage;
use App\Enums\SubscriptionStatus;
use App\Models\PartnerSubscription;
use App\Models\PlatformLead;
use App\Models\Quotation;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ActivatePlatformLeadSubscription
{
    public function __construct(
        private LogPlatformLeadActivity $logActivity,
        private UpdatePlatformLeadStage $updateStage,
    ) {}

    /**
     * @param  array{
     *     rera_number?: string|null,
     *     gst_number?: string|null,
     * }  $data
     * @return array{
     *     lead: PlatformLead,
     *     quotation: Quotation,
     *     tenant: Tenant,
     *     subscription: PartnerSubscription,
     *     login_url: string
     * }
     */
    public function handle(PlatformLead $lead, array $data, ?User $actor = null): array
    {
        if (! $lead->canActivateSubscription()) {
            throw ValidationException::withMessages([
                'lead' => __('Activate subscription requires a paid invoice and an active trial workspace.'),
            ]);
        }

        $quotation = $lead->acceptedQuotation();
        $invoice = $lead->paidInvoiceForAcceptedQuotation();
        $tenant = $lead->tenant;
        $subscription = $lead->latestPartnerSubscription();

        if ($quotation === null || $invoice === null || $tenant === null || $subscription === null) {
            throw ValidationException::withMessages([
                'lead' => __('Activate subscription requires a paid invoice and trial workspace.'),
            ]);
        }

        $quotation->loadMissing('plan');

        return DB::transaction(function () use ($lead, $data, $quotation, $invoice, $tenant, $subscription, $actor): array {
            $amount = max((int) $quotation->plan_price - (int) $quotation->discount_amount, 0);
            $nextBillingAt = $quotation->billing_cycle === BillingCycle::Annual
                ? now()->addYearNoOverflow()
                : now()->addMonthNoOverflow();

            $subscription->update([
                'plan_id' => $quotation->plan_id,
                'billing_cycle' => $quotation->billing_cycle,
                'amount' => $amount,
                'status' => SubscriptionStatus::Active,
                'trial_ends_at' => null,
                'next_billing_at' => $nextBillingAt,
            ]);

            $tenant->update([
                'plan_key' => $quotation->plan?->key,
                'billing_cycle' => $quotation->billing_cycle?->value,
            ]);

            if (isset($data['rera_number']) || isset($data['gst_number'])) {
                $tenantData = $tenant->data ?? [];
                if (filled($data['rera_number'] ?? null)) {
                    $tenantData['rera_number'] = $data['rera_number'];
                }
                if (filled($data['gst_number'] ?? null)) {
                    $tenantData['gst_number'] = $data['gst_number'];
                }
                $tenant->update(['data' => $tenantData]);
            }

            $lead->update([
                'onboarded_at' => now(),
                'rera_number' => $data['rera_number'] ?? $lead->rera_number ?? $quotation->rera_number,
                'gst_number' => $data['gst_number'] ?? $lead->gst_number ?? $quotation->gst_number,
            ]);

            $quotation->update([
                'tenant_id' => $tenant->getTenantKey(),
                'partner_subscription_id' => $subscription->id,
                'onboarded_at' => now(),
            ]);

            $invoice->update([
                'tenant_id' => $tenant->getTenantKey(),
                'partner_subscription_id' => $subscription->id,
            ]);

            $this->logActivity->handle(
                $lead,
                PlatformLeadActivityType::SubscriptionActivated,
                __('Paid subscription activated on existing trial workspace'),
                $actor,
                ['tenant_id' => $tenant->getTenantKey()],
            );

            $this->updateStage->handle($lead->refresh(), PlatformLeadStage::Live, $actor);

            return [
                'lead' => $lead->refresh(),
                'quotation' => $quotation->refresh(),
                'tenant' => $tenant->refresh(),
                'subscription' => $subscription->refresh(),
                'login_url' => route('tenant.login', ['tenant' => $tenant->id]),
            ];
        });
    }
}
