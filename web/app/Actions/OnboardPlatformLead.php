<?php

namespace App\Actions;

use App\Enums\BillingCycle;
use App\Enums\PlatformLeadActivityType;
use App\Enums\PlatformLeadStage;
use App\Enums\SubscriptionStatus;
use App\Enums\TenantStatus;
use App\Models\PartnerSubscription;
use App\Models\PlatformLead;
use App\Models\Quotation;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class OnboardPlatformLead
{
    public function __construct(
        private CreateTenant $createTenant,
        private LogPlatformLeadActivity $logActivity,
        private UpdatePlatformLeadStage $updateStage,
    ) {}

    /**
     * @param  array{
     *     admin_name: string,
     *     admin_email: string,
     *     slug?: string|null,
     *     rera_number?: string|null,
     *     gst_number?: string|null,
     * }  $data
     * @return array{
     *     lead: PlatformLead,
     *     quotation: Quotation,
     *     tenant: Tenant,
     *     subscription: PartnerSubscription,
     *     admin_name: string,
     *     admin_email: string,
     *     admin_password: string,
     *     login_url: string
     * }
     */
    public function handle(PlatformLead $lead, array $data, ?User $actor = null): array
    {
        if (! $lead->canOnboard()) {
            throw ValidationException::withMessages([
                'lead' => __('Onboarding requires a paid invoice for an accepted quotation and no existing workspace.'),
            ]);
        }

        if ($lead->hasLinkedAccount()) {
            throw ValidationException::withMessages([
                'lead' => __('This lead already has a workspace. Use Activate Subscription instead.'),
            ]);
        }

        $quotation = $lead->acceptedQuotation();
        $invoice = $lead->paidInvoiceForAcceptedQuotation();

        if ($quotation === null || $invoice === null) {
            throw ValidationException::withMessages([
                'lead' => __('Onboarding requires a paid invoice for an accepted quotation.'),
            ]);
        }

        $quotation->loadMissing('plan');

        if (trim((string) ($data['admin_name'] ?? '')) === '' || trim((string) ($data['admin_email'] ?? '')) === '') {
            throw ValidationException::withMessages([
                'admin_email' => __('Admin name and email are required.'),
            ]);
        }

        $adminPassword = Str::password(16);

        $slug = trim((string) ($data['slug'] ?? ''));

        if ($slug === '') {
            $slug = $this->uniqueSlugFromName($quotation->company_name ?: $lead->company_name);
        }

        // Create tenant first (involves multi-database operations: central DB + tenant DB migrations)
        // This cannot be wrapped in a transaction as it spans multiple database connections
        $tenant = $this->createTenant->handle([
            'slug' => $slug,
            'name' => (string) ($quotation->company_name ?: $lead->company_name),
            'email' => $quotation->email ?: $lead->email,
            'status' => TenantStatus::Active->value,
            'admin_name' => $data['admin_name'],
            'admin_email' => $data['admin_email'],
            'admin_password' => $adminPassword,
            'owner_name' => $quotation->owner_name ?: $lead->contact_person,
            'phone' => $quotation->phone ?: $lead->phone,
            'plan_key' => $quotation->plan?->key,
            'billing_cycle' => $quotation->billing_cycle?->value,
            'rera_number' => $data['rera_number'] ?? $quotation->rera_number ?? $lead->rera_number,
            'gst_number' => $data['gst_number'] ?? $quotation->gst_number ?? $lead->gst_number,
        ]);

        // Wrap only central database updates in a transaction
        $centralConnection = config('tenancy.database.central_connection', 'mysql');

        return DB::connection($centralConnection)->transaction(function () use ($lead, $data, $quotation, $invoice, $actor, $adminPassword, $tenant): array {
            $amount = max((int) $quotation->plan_price - (int) $quotation->discount_amount, 0);
            $startedAt = now();
            $nextBillingAt = $quotation->billing_cycle === BillingCycle::Annual
                ? $startedAt->copy()->addYearNoOverflow()
                : $startedAt->copy()->addMonthNoOverflow();

            $subscription = PartnerSubscription::query()->create([
                'tenant_id' => $tenant->getTenantKey(),
                'plan_id' => $quotation->plan_id,
                'billing_cycle' => $quotation->billing_cycle,
                'amount' => $amount,
                'currency' => $quotation->plan?->currency ?: 'INR',
                'status' => SubscriptionStatus::Active,
                'started_at' => $startedAt,
                'trial_ends_at' => null,
                'next_billing_at' => $nextBillingAt,
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

            $lead->update([
                'tenant_id' => $tenant->getTenantKey(),
                'onboarded_at' => now(),
                'rera_number' => $data['rera_number'] ?? $lead->rera_number ?? $quotation->rera_number,
                'gst_number' => $data['gst_number'] ?? $lead->gst_number ?? $quotation->gst_number,
            ]);

            $this->logActivity->handle(
                $lead,
                PlatformLeadActivityType::Onboarded,
                __('Channel Partner onboarded'),
                $actor,
                ['tenant_id' => $tenant->getTenantKey()],
            );

            $this->updateStage->handle($lead->refresh(), PlatformLeadStage::Live, $actor);

            return [
                'lead' => $lead->refresh(),
                'quotation' => $quotation->refresh(),
                'tenant' => $tenant->refresh(),
                'subscription' => $subscription->refresh(),
                'admin_name' => $data['admin_name'],
                'admin_email' => $data['admin_email'],
                'admin_password' => $adminPassword,
                'login_url' => route('tenant.login', ['tenant' => $tenant->id]),
            ];
        });
    }

    private function uniqueSlugFromName(string $name): string
    {
        $base = Str::lower(preg_replace('/[^a-z0-9]+/i', '', Str::ascii($name)) ?: 'partner');
        $base = preg_replace('/^[0-9]+/', '', $base) ?: 'partner';
        $base = Str::limit($base, 28, '');

        $slug = $base;
        $suffix = 1;

        while (
            in_array($slug, Tenant::ReservedIds, true)
            || Tenant::query()->whereKey($slug)->exists()
        ) {
            $slug = Str::limit($base, 28, '').$suffix;
            $suffix++;
        }

        return $slug;
    }
}
