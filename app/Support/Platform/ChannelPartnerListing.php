<?php

namespace App\Support\Platform;

use App\Contracts\PlatformPlanCatalog;
use App\Enums\AccountStatus;
use App\Enums\SubscriptionStatus;
use App\Enums\TenantStatus;
use App\Models\PartnerSubscription;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Throwable;

class ChannelPartnerListing
{
    public function __construct(
        private PlatformPlanCatalog $plans,
        private ResolvePartnerAccountStatus $resolvePartnerAccountStatus,
    ) {}

    /**
     * @return array{
     *     tenants: LengthAwarePaginator,
     *     statistics: array{total: int, active: int, trial: int, inactive: int, cancelled: int, trial_ended: int, past_due: int, suspended: int},
     *     filters: array{search: string, status: string|null}
     * }
     */
    public function forRequest(Request $request): array
    {
        $search = trim((string) $request->string('search'));
        $status = $request->string('status')->toString() ?: null;
        $suspendedTenantIds = Tenant::query()
            ->where('status', TenantStatus::Suspended)
            ->pluck('id');
        $latestSubscriptions = PartnerSubscription::query()
            ->whereIn('id', PartnerSubscription::query()
                ->selectRaw('MAX(id)')
                ->groupBy('tenant_id'))
            ->get(['tenant_id', 'status']);
        $accountSubscriptions = $latestSubscriptions
            ->reject(fn (PartnerSubscription $subscription): bool => $suspendedTenantIds->contains($subscription->tenant_id));

        $query = Tenant::query()
            ->with('partnerSubscriptions')
            ->latest()
            ->orderByDesc('id');

        if ($search !== '') {
            $query->where(function ($builder) use ($search): void {
                $builder
                    ->where('name', 'like', '%'.$search.'%')
                    ->orWhere('email', 'like', '%'.$search.'%')
                    ->orWhere('id', 'like', '%'.$search.'%');
            });
        }

        if ($status === AccountStatus::Suspended->value) {
            $query->whereIn('id', $suspendedTenantIds);
        } elseif ($status !== null) {
            $subscriptionStatuses = match ($status) {
                AccountStatus::Active->value => [SubscriptionStatus::Active, SubscriptionStatus::PastDue],
                AccountStatus::Trial->value => [SubscriptionStatus::Trial],
                AccountStatus::Inactive->value => [SubscriptionStatus::Paused],
                AccountStatus::Cancelled->value => [SubscriptionStatus::Cancelled],
                AccountStatus::TrialEnded->value => [SubscriptionStatus::TrialEnded],
                'past_due' => [SubscriptionStatus::PastDue],
                default => [],
            };

            if ($subscriptionStatuses !== []) {
                $query
                    ->where('status', TenantStatus::Active)
                    ->whereIn('id', $accountSubscriptions
                        ->whereIn('status', $subscriptionStatuses)
                        ->pluck('tenant_id'));
            }
        }

        /** @var LengthAwarePaginator<int, Tenant> $tenants */
        $tenants = $query->paginate(15)->withQueryString();

        $tenantIdsWithActiveSubscription = PartnerSubscription::query()
            ->active()
            ->whereIn('tenant_id', $tenants->getCollection()->pluck('id'))
            ->pluck('tenant_id')
            ->unique()
            ->all();

        $tenants->getCollection()->transform(function (Tenant $tenant) use ($tenantIdsWithActiveSubscription): Tenant {
            $planKey = $tenant->getAttribute('plan_key');
            $plan = $this->plans->find(is_string($planKey) ? $planKey : null);
            $account = $this->resolvePartnerAccountStatus->forTenant($tenant);

            $tenant->setAttribute('users_count', $this->activeUsersCount($tenant));
            $tenant->setAttribute('plan_label', $plan['label'] ?? '—');
            $tenant->setAttribute('users_limit', $plan['limits']['users'] ?? null);
            $tenant->setAttribute('last_active_label', $tenant->updated_at?->diffForHumans() ?? '—');
            $tenant->setAttribute('account_status', $account['status']?->value);
            $tenant->setAttribute('account_status_shows_due', $account['show_due']);
            $tenant->setAttribute(
                'can_be_deleted',
                ! in_array($tenant->id, $tenantIdsWithActiveSubscription, true),
            );

            return $tenant;
        });

        return [
            'tenants' => $tenants,
            'statistics' => [
                'total' => Tenant::query()->count(),
                'active' => $accountSubscriptions->whereIn('status', [SubscriptionStatus::Active, SubscriptionStatus::PastDue])->count(),
                'trial' => $accountSubscriptions->where('status', SubscriptionStatus::Trial)->count(),
                'inactive' => $accountSubscriptions->where('status', SubscriptionStatus::Paused)->count(),
                'cancelled' => $accountSubscriptions->where('status', SubscriptionStatus::Cancelled)->count(),
                'trial_ended' => $accountSubscriptions->where('status', SubscriptionStatus::TrialEnded)->count(),
                'past_due' => $accountSubscriptions->where('status', SubscriptionStatus::PastDue)->count(),
                'suspended' => $suspendedTenantIds->count(),
            ],
            'filters' => [
                'search' => $search,
                'status' => $status,
            ],
        ];
    }

    private function activeUsersCount(Tenant $tenant): int
    {
        try {
            return (int) $tenant->run(fn (): int => User::query()->where('is_active', true)->count());
        } catch (Throwable) {
            return 0;
        }
    }
}
