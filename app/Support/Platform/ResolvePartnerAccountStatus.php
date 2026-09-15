<?php

namespace App\Support\Platform;

use App\Enums\AccountStatus;
use App\Enums\SubscriptionStatus;
use App\Enums\TenantStatus;
use App\Models\PartnerSubscription;
use App\Models\PlatformLead;
use App\Models\Tenant;

class ResolvePartnerAccountStatus
{
    /**
     * @return array{status: AccountStatus|null, show_due: bool}
     */
    public function forLead(PlatformLead $lead): array
    {
        if (! $lead->hasLinkedAccount()) {
            return ['status' => null, 'show_due' => false];
        }

        $tenant = $lead->relationLoaded('tenant')
            ? $lead->tenant
            : $lead->tenant()->first();

        if ($tenant !== null) {
            return $this->forTenant($tenant);
        }

        return $this->forSubscription($lead->latestPartnerSubscription());
    }

    /**
     * @return array{status: AccountStatus|null, show_due: bool}
     */
    public function forTenant(Tenant $tenant): array
    {
        if ($tenant->status === TenantStatus::Suspended) {
            return ['status' => AccountStatus::Suspended, 'show_due' => false];
        }

        $subscription = $tenant->relationLoaded('partnerSubscriptions')
            ? $tenant->partnerSubscriptions->sortByDesc('id')->first()
            : $tenant->partnerSubscriptions()->latest('id')->first();

        return $this->forSubscription($subscription);
    }

    /**
     * @return array{status: AccountStatus|null, show_due: bool}
     */
    public function forSubscription(?PartnerSubscription $subscription): array
    {
        if ($subscription === null) {
            return ['status' => null, 'show_due' => false];
        }

        $showDue = $subscription->status === SubscriptionStatus::PastDue;

        $status = match ($subscription->status) {
            SubscriptionStatus::Trial => AccountStatus::Trial,
            SubscriptionStatus::Active, SubscriptionStatus::PastDue => AccountStatus::Active,
            SubscriptionStatus::Paused => AccountStatus::Inactive,
            SubscriptionStatus::Cancelled => AccountStatus::Cancelled,
            SubscriptionStatus::TrialEnded => AccountStatus::TrialEnded,
            default => AccountStatus::Inactive,
        };

        return ['status' => $status, 'show_due' => $showDue];
    }
}
