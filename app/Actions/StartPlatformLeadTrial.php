<?php

namespace App\Actions;

use App\Enums\BillingCycle;
use App\Enums\PlatformLeadActivityType;
use App\Enums\PlatformLeadStage;
use App\Enums\SubscriptionStatus;
use App\Enums\TenantStatus;
use App\Models\PartnerSubscription;
use App\Models\Plan;
use App\Models\PlatformLead;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class StartPlatformLeadTrial
{
    public function __construct(
        private CreateTenant $createTenant,
        private LogPlatformLeadActivity $logActivity,
        private UpdatePlatformLeadStage $updateStage,
    ) {}

    /**
     * @param  array{
     *     company_name: string,
     *     email: string,
     *     phone?: string|null,
     *     plan_id: int,
     *     trial_days: int,
     *     slug?: string|null,
     * }  $data
     * @return array{
     *     lead: PlatformLead,
     *     tenant: Tenant,
     *     subscription: PartnerSubscription,
     *     admin_name: string,
     *     admin_email: string,
     *     admin_password: string,
     *     login_url: string,
     *     plan_name: string,
     *     trial_days: int,
     *     trial_ends_at: string,
     * }
     */
    public function handle(PlatformLead $lead, array $data, ?User $user = null): array
    {
        if (! $lead->canStartTrial()) {
            throw ValidationException::withMessages([
                'lead' => __('This lead already has a workspace and cannot start a new trial.'),
            ]);
        }

        $plan = Plan::query()->findOrFail($data['plan_id']);
        $trialDays = max(1, min(90, (int) $data['trial_days']));
        $adminPassword = Str::password(16);
        $adminName = $lead->contact_person;
        $adminEmail = $data['email'];
        $slug = trim((string) ($data['slug'] ?? ''));

        if ($slug === '') {
            $slug = $this->uniqueSlugFromName($data['company_name']);
        }

        $lead->update([
            'company_name' => $data['company_name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? $lead->phone,
            'rera_number' => $data['rera_number'] ?? $lead->rera_number,
            'gst_number' => $data['gst_number'] ?? $lead->gst_number,
        ]);

        $tenant = $this->createTenant->handle([
            'slug' => $slug,
            'name' => $data['company_name'],
            'email' => $data['email'],
            'status' => TenantStatus::Active->value,
            'admin_name' => $adminName,
            'admin_email' => $adminEmail,
            'admin_password' => $adminPassword,
            'owner_name' => $lead->contact_person,
            'phone' => $data['phone'] ?? $lead->phone,
            'plan_key' => $plan->key,
            'billing_cycle' => BillingCycle::Monthly->value,
        ]);

        $startedAt = now();
        $trialEndsAt = $startedAt->copy()->addDays($trialDays);

        $subscription = PartnerSubscription::query()->create([
            'tenant_id' => $tenant->getTenantKey(),
            'plan_id' => $plan->id,
            'billing_cycle' => BillingCycle::Monthly,
            'amount' => (int) $plan->price_monthly,
            'currency' => $plan->currency ?: 'INR',
            'status' => SubscriptionStatus::Trial,
            'started_at' => $startedAt,
            'trial_ends_at' => $trialEndsAt,
            'next_billing_at' => $trialEndsAt,
        ]);

        $lead->update(['tenant_id' => $tenant->getTenantKey()]);

        $this->logActivity->handle(
            $lead,
            PlatformLeadActivityType::TrialStarted,
            __('Trial started for :days days on :plan', [
                'days' => $trialDays,
                'plan' => $plan->name,
            ]),
            $user,
        );

        $this->updateStage->handle($lead->refresh(), PlatformLeadStage::Trial, $user);

        return [
            'lead' => $lead->refresh(),
            'tenant' => $tenant,
            'subscription' => $subscription,
            'admin_name' => $adminName,
            'admin_email' => $adminEmail,
            'admin_password' => $adminPassword,
            'login_url' => route('tenant.login', ['tenant' => $tenant->id]),
            'plan_name' => $plan->name,
            'trial_days' => $trialDays,
            'trial_ends_at' => $trialEndsAt->timezone(config('app.timezone'))->format('d M Y'),
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
