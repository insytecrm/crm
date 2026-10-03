<?php

namespace App\Actions;

use App\Enums\TenantStatus;
use App\Models\PartnerSubscription;
use App\Models\Quotation;
use App\Models\Tenant;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class OnboardQuotationPartner
{
    public function __construct(
        private CreateTenant $createTenant,
        private CreateSubscriptionFromQuotation $createSubscriptionFromQuotation,
        private SyncPlatformLeadFromQuotation $syncLead,
    ) {}

    /**
     * @param  array{
     *     admin_name: string,
     *     admin_email: string,
     *     slug?: string|null
     * }  $data
     * @return array{
     *     quotation: Quotation,
     *     tenant: Tenant,
     *     subscription: PartnerSubscription,
     *     admin_name: string,
     *     admin_email: string,
     *     admin_password: string,
     *     login_url: string
     * }
     */
    public function handle(Quotation $quotation, array $data): array
    {
        if (! $quotation->canStartOnboarding()) {
            throw ValidationException::withMessages([
                'quotation' => __('Only accepted quotations without an onboarded Channel Partner can start onboarding.'),
            ]);
        }

        $quotation->loadMissing('plan');

        $adminPassword = Str::password(16);
        $slug = trim((string) ($data['slug'] ?? ''));
        if ($slug === '') {
            $slug = $this->uniqueSlugFromName($quotation->company_name ?: 'partner');
        }

        $tenant = $this->createTenant->handle([
            'slug' => $slug,
            'name' => (string) $quotation->company_name,
            'email' => $quotation->email,
            'status' => TenantStatus::Active->value,
            'admin_name' => $data['admin_name'],
            'admin_email' => $data['admin_email'],
            'admin_password' => $adminPassword,
            'owner_name' => $quotation->owner_name,
            'phone' => $quotation->phone,
            'plan_key' => $quotation->plan?->key,
            'billing_cycle' => $quotation->billing_cycle?->value,
            'trial_days' => $quotation->trial_enabled ? (int) $quotation->trial_days : null,
        ]);

        $quotation->update([
            'tenant_id' => $tenant->getTenantKey(),
            'onboarded_at' => now(),
        ]);

        $subscription = $this->createSubscriptionFromQuotation->handle($quotation->refresh());

        $this->syncLead->afterOnboarded($quotation->refresh(), $tenant->getTenantKey());

        return [
            'quotation' => $quotation->refresh(),
            'tenant' => $tenant,
            'subscription' => $subscription,
            'admin_name' => $data['admin_name'],
            'admin_email' => $data['admin_email'],
            'admin_password' => $adminPassword,
            'login_url' => route('tenant.login', ['tenant' => $tenant->id]),
        ];
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
