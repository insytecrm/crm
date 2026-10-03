<?php

namespace App\Support\Platform;

use App\Enums\FacebookPageConnectionStatus;
use App\Enums\GoogleSheetConnectionStatus;
use App\Enums\LeadActivityType;
use App\Enums\PlanCapability;
use App\Enums\PlanFeature;
use App\Enums\PlanLimitKey;
use App\Models\AiMessage;
use App\Models\Automation;
use App\Models\AutomationRun;
use App\Models\FacebookPageConnection;
use App\Models\GoogleSheetConnection;
use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\Plan;
use App\Models\PortalWebhookEndpoint;
use App\Models\Property;
use App\Models\SalesTeam;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class TenantPlanAccess
{
    private ?PlanEntitlement $entitlement = null;

    private ?string $entitlementContext = null;

    public function entitlement(): PlanEntitlement
    {
        $context = $this->contextKey();

        if ($this->entitlement !== null && $this->entitlementContext === $context) {
            return $this->entitlement;
        }

        $this->entitlementContext = $context;
        $tenant = tenant();

        if (! $tenant instanceof Tenant) {
            return $this->entitlement = PlanEntitlement::unrestricted();
        }

        $planKey = $tenant->getAttribute('plan_key');

        if (! is_string($planKey) || $planKey === '') {
            return $this->entitlement = PlanEntitlement::unrestricted();
        }

        $plan = Plan::query()->where('key', $planKey)->first();

        if ($plan === null) {
            return $this->entitlement = PlanEntitlement::unrestricted();
        }

        return $this->entitlement = PlanEntitlement::fromPlan($plan);
    }

    private function contextKey(): string
    {
        $tenant = tenant();

        if (! $tenant instanceof Tenant) {
            return 'none';
        }

        return (string) $tenant->getTenantKey().':'.(string) $tenant->getAttribute('plan_key');
    }

    public function hasFeature(PlanFeature|string $feature): bool
    {
        return $this->entitlement()->hasFeature($feature);
    }

    public function hasCapability(PlanCapability|string $capability): bool
    {
        return $this->entitlement()->hasCapability($capability);
    }

    public function abortUnlessFeature(PlanFeature|string $feature): void
    {
        abort_unless($this->hasFeature($feature), 403);
    }

    public function abortUnlessCapability(PlanCapability|string $capability): void
    {
        abort_unless($this->hasCapability($capability), 403);
    }

    /**
     * @return int|null Null means unlimited.
     */
    public function limit(PlanLimitKey|string $key): ?int
    {
        return $this->entitlement()->limit($key);
    }

    public function used(PlanLimitKey $key): int
    {
        $monthStart = now()->startOfMonth();

        return match ($key) {
            PlanLimitKey::Users => User::query()->where('is_active', true)->count(),
            PlanLimitKey::Leads => Lead::query()->count(),
            PlanLimitKey::Automations => Automation::query()->count(),
            PlanLimitKey::AutomationRunsMonthly => AutomationRun::query()
                ->where('dry_run', false)
                ->where('created_at', '>=', $monthStart)
                ->count(),
            PlanLimitKey::WhatsAppMessagesMonthly => LeadActivity::query()
                ->where('type', LeadActivityType::WhatsAppMessage)
                ->where('created_at', '>=', $monthStart)
                ->count(),
            PlanLimitKey::AiMessagesMonthly => AiMessage::query()
                ->where('role', 'user')
                ->where('created_at', '>=', $monthStart)
                ->count(),
            PlanLimitKey::Integrations => $this->connectedIntegrationsCount(),
            PlanLimitKey::Properties => Property::query()->count(),
            PlanLimitKey::Teams => SalesTeam::query()->count(),
            PlanLimitKey::Microsites => Property::query()->where('microsite_enabled', true)->count(),
        };
    }

    public function remaining(PlanLimitKey $key): ?int
    {
        $limit = $this->limit($key);

        if ($limit === null) {
            return null;
        }

        return max(0, $limit - $this->used($key));
    }

    public function canConsume(PlanLimitKey $key, int $amount = 1): bool
    {
        $limit = $this->limit($key);

        if ($limit === null) {
            return true;
        }

        return ($this->used($key) + $amount) <= $limit;
    }

    public function assertCanConsume(PlanLimitKey $key, int $amount = 1): void
    {
        if ($this->canConsume($key, $amount)) {
            return;
        }

        $limit = $this->limit($key) ?? 0;

        throw ValidationException::withMessages([
            'plan' => __('This plan allows :limit :label.', [
                'limit' => number_format($limit),
                'label' => Str::lower($key->label()),
            ]),
        ]);
    }

    private function connectedIntegrationsCount(): int
    {
        $count = 0;

        $tenant = tenant();

        if ($tenant instanceof Tenant && filled($tenant->lead_api_token_hash)) {
            $count++;
        }

        $count += GoogleSheetConnection::query()
            ->where('status', GoogleSheetConnectionStatus::Connected)
            ->count();

        $count += FacebookPageConnection::query()
            ->where('status', FacebookPageConnectionStatus::Connected)
            ->count();

        $count += PortalWebhookEndpoint::query()->count();

        return $count;
    }
}
