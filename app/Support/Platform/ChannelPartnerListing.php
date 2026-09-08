<?php

namespace App\Support\Platform;

use App\Contracts\PlatformPlanCatalog;
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
    public function __construct(private PlatformPlanCatalog $plans) {}

    /**
     * @return array{
     *     tenants: LengthAwarePaginator,
     *     statistics: array{total: int, active: int, trial: int, past_due: int, suspended: int},
     *     filters: array{search: string, status: string|null}
     * }
     */
    public function forRequest(Request $request): array
    {
        $search = trim((string) $request->string('search'));
        $status = $request->string('status')->toString() ?: null;

        $query = Tenant::query()
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

        if ($status === TenantStatus::Active->value || $status === TenantStatus::Suspended->value) {
            $query->where('status', $status);
        } elseif (in_array($status, ['trial', 'past_due'], true)) {
            $tenantIds = PartnerSubscription::query()
                ->where('status', $status === 'trial' ? SubscriptionStatus::Trial : SubscriptionStatus::PastDue)
                ->pluck('tenant_id');

            $query->whereIn('id', $tenantIds);
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

            $tenant->setAttribute('users_count', $this->activeUsersCount($tenant));
            $tenant->setAttribute('plan_label', $plan['label'] ?? '—');
            $tenant->setAttribute('users_limit', $plan['limits']['users'] ?? null);
            $tenant->setAttribute('last_active_label', $tenant->updated_at?->diffForHumans() ?? '—');
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
                'active' => Tenant::query()->where('status', TenantStatus::Active)->count(),
                'trial' => PartnerSubscription::query()->where('status', SubscriptionStatus::Trial)->pluck('tenant_id')->unique()->count(),
                'past_due' => PartnerSubscription::query()->where('status', SubscriptionStatus::PastDue)->pluck('tenant_id')->unique()->count(),
                'suspended' => Tenant::query()->where('status', TenantStatus::Suspended)->count(),
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
