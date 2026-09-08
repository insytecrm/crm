<?php

namespace App\Support\Platform;

use App\Contracts\ChannelPartnerProfileData;
use App\Contracts\PlatformPlanCatalog;
use App\Models\BillingInvoice;
use App\Models\PartnerSubscription;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Throwable;

class EloquentChannelPartnerProfileData implements ChannelPartnerProfileData
{
    /**
     * @var array<string, array{
     *     users_active: int,
     *     users_active_this_month: int,
     *     leads_total: int,
     *     leads_this_month: int,
     *     automations_total: int,
     *     automation_runs_this_month: int,
     *     site_visits_this_month: int,
     *     messages_this_month: int,
     *     integrations: list<array{
     *         key: string,
     *         label: string,
     *         status: string,
     *         status_label: string,
     *         last_sync_label: string|null,
     *         attention_message: string|null,
     *         available: bool,
     *         actions: list<array{label: string, href: string|null, disabled: bool}>
     *     }>,
     *     activity_events: list<array{group_label: string, time: string, description: string, category: string, sort: int}>
     * }>
     */
    private array $snapshots = [];

    public function __construct(
        private PlatformPlanCatalog $plans,
        private ChannelPartnerTenantSnapshot $tenantSnapshot,
    ) {}

    public function shell(Tenant $tenant, string $activeTab): array
    {
        $plan = $this->plans->find($this->stringAttribute($tenant, 'plan_key'));
        $status = $tenant->status?->value ?? 'active';
        $canDelete = ! $tenant->hasActiveSubscription();

        $tabs = [
            ['key' => 'overview', 'label' => __('Overview'), 'route' => 'tenants.show'],
            ['key' => 'users', 'label' => __('Users'), 'route' => 'tenants.users'],
            ['key' => 'subscription', 'label' => __('Subscription'), 'route' => 'tenants.subscription'],
            ['key' => 'usage', 'label' => __('Usage'), 'route' => 'tenants.usage'],
            ['key' => 'integrations', 'label' => __('Integrations'), 'route' => 'tenants.integrations'],
            ['key' => 'activity', 'label' => __('Activity'), 'route' => 'tenants.activity'],
        ];

        return [
            'name' => $tenant->name,
            'status' => $status,
            'status_label' => ucfirst(str_replace('_', ' ', $status)),
            'plan_key' => $plan['key'] ?? null,
            'plan_label' => $plan['label'] ?? '—',
            'owner_name' => $this->stringAttribute($tenant, 'owner_name') ?: $this->primaryAdminName($tenant),
            'joined_label' => $tenant->created_at?->timezone(config('app.timezone'))->format('d M Y') ?? '—',
            'joined_at' => $tenant->created_at?->toIso8601String(),
            'workspace_url' => route('tenant.login', ['tenant' => $tenant->id]),
            'tabs' => collect($tabs)
                ->map(fn (array $tab): array => [
                    'key' => $tab['key'],
                    'label' => $tab['label'],
                    'href' => route($tab['route'], $tenant),
                    'active' => $activeTab === $tab['key'],
                ])
                ->all(),
            'more_actions' => [
                [
                    'label' => __('Delete Channel Partner'),
                    'href' => $canDelete ? route('tenants.destroy', $tenant) : null,
                    'method' => 'DELETE',
                    'danger' => true,
                    'disabled' => ! $canDelete,
                ],
            ],
        ];
    }

    public function overview(Tenant $tenant): array
    {
        $snapshot = $this->snapshot($tenant);
        $plan = $this->plans->find($this->stringAttribute($tenant, 'plan_key'));
        $usersUsed = $snapshot['users_active'];
        $usersLimit = $plan['limits']['users'] ?? null;
        $joinedShort = $tenant->created_at?->timezone(config('app.timezone'))->format('d M') ?? '—';
        $usageMeters = $this->usageMeters($snapshot, $plan);

        return [
            'account' => [
                ['label' => __('Plan'), 'value' => $plan['label'] ?? '—'],
                ['label' => __('Status'), 'value' => ucfirst($tenant->status?->value ?? 'active')],
                ['label' => __('Users'), 'value' => $this->ratioLabel($usersUsed, $usersLimit)],
                ['label' => __('Joined'), 'value' => $joinedShort],
                [
                    'label' => __('Next Billing'),
                    'value' => $this->stringAttribute($tenant, 'next_billing_label') ?: '—',
                ],
            ],
            'usage' => collect($usageMeters)
                ->map(fn (array $meter): array => [
                    'label' => $meter['label'],
                    'used_label' => $meter['used_label'],
                    'limit_label' => $meter['limit_label'],
                    'percent' => $meter['percent'],
                ])
                ->all(),
            'integrations' => collect($snapshot['integrations'])
                ->filter(fn (array $item): bool => $item['available'])
                ->map(fn (array $item): array => [
                    'key' => $item['key'],
                    'label' => $item['label'],
                    'status' => $item['status'],
                    'status_label' => $item['status_label'],
                ])
                ->values()
                ->all(),
            'recent_activity' => $this->recentActivityEvents($tenant, $snapshot, 6),
            'attention' => $this->attentionItems($tenant, $snapshot),
        ];
    }

    public function users(Tenant $tenant, Request $request): array
    {
        $search = trim((string) $request->string('search'));
        $role = $request->string('role')->toString() ?: null;
        $status = $request->string('status')->toString() ?: null;

        $users = [];
        $total = 0;
        $editRoles = [];

        try {
            $tenant->run(function () use (&$users, &$total, &$editRoles, $search, $role, $status, $tenant): void {
                $query = User::query()->with('role')->orderBy('name');

                if ($search !== '') {
                    $query->where(function ($builder) use ($search): void {
                        $builder
                            ->where('name', 'like', '%'.$search.'%')
                            ->orWhere('email', 'like', '%'.$search.'%');
                    });
                }

                if ($role) {
                    $query->whereHas('role', fn ($builder) => $builder->where('slug', $role));
                }

                if ($status === 'active') {
                    $query->where('is_active', true);
                } elseif ($status === 'inactive') {
                    $query->where('is_active', false);
                }

                $total = (clone $query)->count();

                $editRoles = Role::query()
                    ->where('is_active', true)
                    ->orderBy('name')
                    ->get()
                    ->map(fn (Role $item): array => [
                        'id' => $item->id,
                        'name' => $item->name,
                    ])
                    ->all();

                $users = $query->limit(100)->get()->map(function (User $user) use ($tenant): array {
                    $isActive = $user->isActive();

                    return [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'role' => $user->role?->name ?? __('User'),
                        'role_id' => $user->role_id,
                        'status' => $isActive ? 'active' : 'inactive',
                        'status_label' => $isActive ? __('Active') : __('Inactive'),
                        'last_active_label' => $user->updated_at?->diffForHumans() ?? '—',
                        'created_label' => $user->created_at?->timezone(config('app.timezone'))->format('d M Y') ?? '—',
                        'actions' => [
                            [
                                'label' => __('View'),
                                'href' => null,
                                'method' => null,
                                'modal' => 'view-partner-user-'.$user->id,
                                'disabled' => false,
                                'confirm' => null,
                                'danger' => false,
                            ],
                            [
                                'label' => __('Edit'),
                                'href' => null,
                                'method' => null,
                                'modal' => 'edit-partner-user-'.$user->id,
                                'disabled' => false,
                                'confirm' => null,
                                'danger' => false,
                            ],
                            [
                                'label' => $isActive ? __('Disable') : __('Enable'),
                                'href' => route('tenants.users.status', [$tenant, $user->id]),
                                'method' => 'PATCH',
                                'modal' => null,
                                'disabled' => false,
                                'confirm' => $isActive
                                    ? __('Disable this user?')
                                    : null,
                                'danger' => $isActive,
                                'payload' => [
                                    'is_active' => $isActive ? '0' : '1',
                                ],
                            ],
                            [
                                'label' => __('Reset Access'),
                                'href' => route('tenants.users.reset-access', [$tenant, $user->id]),
                                'method' => 'POST',
                                'modal' => null,
                                'disabled' => false,
                                'confirm' => __('Reset access and generate a temporary password?'),
                                'danger' => false,
                                'payload' => [],
                            ],
                        ],
                    ];
                })->all();
            });
        } catch (Throwable) {
            $users = [];
            $total = 0;
            $editRoles = [];
        }

        return [
            'total' => $total,
            'filters' => [
                'search' => $search,
                'role' => $role,
                'status' => $status,
            ],
            'role_options' => [
                ['value' => 'administrator', 'label' => __('Admin')],
                ['value' => 'manager', 'label' => __('Manager')],
                ['value' => 'agent', 'label' => __('Agent')],
            ],
            'status_options' => [
                ['value' => 'active', 'label' => __('Active')],
                ['value' => 'inactive', 'label' => __('Inactive')],
            ],
            'edit_roles' => $editRoles,
            'users' => $users,
        ];
    }

    public function subscription(Tenant $tenant): array
    {
        $subscription = PartnerSubscription::query()
            ->with('plan')
            ->where('tenant_id', $tenant->getTenantKey())
            ->latest('id')
            ->first();

        $plan = $this->plans->find($this->stringAttribute($tenant, 'plan_key'));
        $billingCycle = $this->stringAttribute($tenant, 'billing_cycle') ?: null;
        $status = $tenant->status?->value ?? 'active';

        if ($subscription !== null) {
            $plan = $this->plans->find($subscription->plan?->key) ?? $plan;
            $billingCycle = $subscription->billing_cycle?->value ?? $billingCycle;
            $status = $subscription->status->value;
        }

        $priceLabel = '—';
        if ($subscription !== null) {
            $priceLabel = BillingMoney::format((int) $subscription->amount).($subscription->billing_cycle?->priceSuffix() ?? '');
        } elseif ($plan !== null) {
            $priceLabel = $billingCycle === 'annual'
                ? ($plan['price_annual_label'] ?? $plan['price_monthly_label']).' / '.__('year')
                : ($plan['price_monthly_label'].' / '.__('month'));
        }

        $invoices = BillingInvoice::query()
            ->where('tenant_id', $tenant->getTenantKey())
            ->latest('issued_at')
            ->limit(20)
            ->get()
            ->map(fn (BillingInvoice $invoice): array => [
                'date' => $invoice->issued_at?->timezone(config('app.timezone'))->format('d M Y') ?? '—',
                'invoice' => '#'.$invoice->number,
                'amount' => BillingMoney::format((int) $invoice->total),
                'status' => $invoice->status->value,
                'status_label' => $invoice->status->label(),
            ])
            ->all();

        $started = $subscription?->started_at ?? $tenant->created_at;
        $nextBilling = $subscription?->next_billing_at !== null
            ? $subscription->next_billing_at->timezone(config('app.timezone'))->format('d M Y')
            : ($this->stringAttribute($tenant, 'next_billing_label') ?: '—');

        return [
            'plan_label' => $plan['label'] ?? ($subscription?->plan?->name ?? '—'),
            'price_label' => $priceLabel,
            'status' => $status,
            'status_label' => ucfirst(str_replace('_', ' ', $status)),
            'details' => [
                [
                    'label' => __('Started'),
                    'value' => $started?->timezone(config('app.timezone'))->format('d M Y') ?? '—',
                ],
                [
                    'label' => __('Next Billing'),
                    'value' => $nextBilling,
                ],
                [
                    'label' => __('Billing Cycle'),
                    'value' => match ($billingCycle) {
                        'monthly' => __('Monthly'),
                        'annual' => __('Annual'),
                        default => '—',
                    },
                ],
                [
                    'label' => __('Status'),
                    'value' => ucfirst(str_replace('_', ' ', $status)),
                ],
            ],
            'actions' => [
                [
                    'label' => __('Change Plan'),
                    'href' => $subscription ? route('platform.revenue.subscriptions.show', $subscription).'#change-plan' : null,
                    'variant' => 'outline',
                    'disabled' => $subscription === null,
                ],
                [
                    'label' => __('Extend Trial'),
                    'href' => $subscription ? route('platform.revenue.subscriptions.show', $subscription).'#extend-trial' : null,
                    'variant' => 'outline',
                    'disabled' => $subscription === null,
                ],
                [
                    'label' => __('Apply Discount'),
                    'href' => route('platform.revenue.adjustments.discounts'),
                    'variant' => 'outline',
                    'disabled' => false,
                ],
                [
                    'label' => __('Pause'),
                    'href' => $subscription ? route('platform.revenue.subscriptions.show', $subscription) : null,
                    'variant' => 'outline',
                    'disabled' => $subscription === null,
                ],
                [
                    'label' => __('Cancel'),
                    'href' => $subscription ? route('platform.revenue.subscriptions.show', $subscription) : null,
                    'variant' => 'destructive',
                    'disabled' => $subscription === null,
                ],
            ],
            'invoices' => $invoices,
        ];
    }

    public function usage(Tenant $tenant): array
    {
        $snapshot = $this->snapshot($tenant);
        $plan = $this->plans->find($this->stringAttribute($tenant, 'plan_key'));
        $meters = $this->usageMeters($snapshot, $plan);

        $warnings = [];
        foreach ($meters as $meter) {
            if ($meter['percent'] !== null && $meter['percent'] >= 80) {
                $warnings[] = [
                    'message' => __(':label are at :percent% of the plan limit.', [
                        'label' => $meter['label'],
                        'percent' => (int) round($meter['percent']),
                    ]),
                ];
            }
        }

        return [
            'meters' => $meters,
            'month_activity' => [
                ['label' => __('Active Users'), 'value' => number_format($snapshot['users_active_this_month'])],
                ['label' => __('New Leads'), 'value' => number_format($snapshot['leads_this_month'])],
                ['label' => __('Site Visits'), 'value' => number_format($snapshot['site_visits_this_month'])],
                ['label' => __('Automations'), 'value' => number_format($snapshot['automation_runs_this_month'])],
                ['label' => __('Messages'), 'value' => number_format($snapshot['messages_this_month'])],
            ],
            'warnings' => $warnings,
        ];
    }

    public function integrations(Tenant $tenant): array
    {
        $items = $this->snapshot($tenant)['integrations'];
        $connected = collect($items)->where('status', 'connected')->count();
        $attention = collect($items)->whereIn('status', ['failed', 'needs_attention'])->count();

        return [
            'connected_count' => $connected,
            'attention_count' => $attention,
            'items' => $items,
        ];
    }

    public function activity(Tenant $tenant, Request $request): array
    {
        $filter = $request->string('filter')->toString() ?: 'all';

        $filters = [
            ['key' => 'all', 'label' => __('All Activity')],
            ['key' => 'users', 'label' => __('Users')],
            ['key' => 'billing', 'label' => __('Billing')],
            ['key' => 'integrations', 'label' => __('Integrations')],
            ['key' => 'system', 'label' => __('System')],
        ];

        $events = $this->activityEvents($tenant, $this->snapshot($tenant));

        if ($filter !== 'all') {
            $events = array_values(array_filter(
                $events,
                fn (array $event): bool => $event['category'] === $filter
            ));
        }

        $groups = [];
        foreach ($events as $event) {
            $groups[$event['group_label']][] = [
                'time' => $event['time'],
                'description' => $event['description'],
                'category' => $event['category'],
            ];
        }

        return [
            'filters' => collect($filters)
                ->map(fn (array $item): array => [
                    'key' => $item['key'],
                    'label' => $item['label'],
                    'href' => route('tenants.activity', ['tenant' => $tenant, 'filter' => $item['key'] === 'all' ? null : $item['key']]),
                    'active' => $filter === $item['key'],
                ])
                ->all(),
            'groups' => collect($groups)
                ->map(fn (array $groupEvents, string $label): array => [
                    'label' => $label,
                    'events' => $groupEvents,
                ])
                ->values()
                ->all(),
        ];
    }

    /**
     * @return array{
     *     users_active: int,
     *     users_active_this_month: int,
     *     leads_total: int,
     *     leads_this_month: int,
     *     automations_total: int,
     *     automation_runs_this_month: int,
     *     site_visits_this_month: int,
     *     messages_this_month: int,
     *     integrations: list<array{
     *         key: string,
     *         label: string,
     *         status: string,
     *         status_label: string,
     *         last_sync_label: string|null,
     *         attention_message: string|null,
     *         available: bool,
     *         actions: list<array{label: string, href: string|null, disabled: bool}>
     *     }>,
     *     activity_events: list<array{group_label: string, time: string, description: string, category: string, sort: int}>
     * }
     */
    private function snapshot(Tenant $tenant): array
    {
        $key = (string) $tenant->getTenantKey();

        return $this->snapshots[$key] ??= $this->tenantSnapshot->for($tenant);
    }

    /**
     * @param  array{users_active: int, leads_total: int, automations_total: int, messages_this_month: int, ai_messages_this_month?: int}  $snapshot
     * @param  array{limits: array<string, int|null>}|null  $plan
     * @return list<array{
     *     key: string,
     *     label: string,
     *     used: float|int|null,
     *     limit: float|int|null,
     *     used_label: string,
     *     limit_label: string|null,
     *     unit: string|null,
     *     percent: float|null
     * }>
     */
    private function usageMeters(array $snapshot, ?array $plan): array
    {
        $limits = $plan['limits'] ?? [];

        return [
            $this->meter('users', __('Users'), $snapshot['users_active'], $limits['users'] ?? null),
            $this->meter('leads', __('Leads'), $snapshot['leads_total'], $limits['leads'] ?? null),
            $this->meter('automations', __('Automations'), $snapshot['automations_total'], $limits['automations'] ?? null),
            $this->meter('ai_messages', __('AI messages'), $snapshot['ai_messages_this_month'] ?? 0, $limits['ai_messages_monthly'] ?? null),
            $this->meter('whatsapp', __('WhatsApp'), $snapshot['messages_this_month'], $limits['whatsapp_messages_monthly'] ?? null),
        ];
    }

    /**
     * @return array{
     *     key: string,
     *     label: string,
     *     used: float|int|null,
     *     limit: float|int|null,
     *     used_label: string,
     *     limit_label: string|null,
     *     unit: string|null,
     *     percent: float|null
     * }
     */
    private function meter(string $key, string $label, float|int|null $used, float|int|null $limit, ?string $unit = null): array
    {
        $percent = ($used !== null && $limit !== null && (float) $limit > 0)
            ? min(100, round(((float) $used / (float) $limit) * 100, 1))
            : null;

        if ($used === null) {
            $usedLabel = '—';
        } elseif ($unit) {
            $usedLabel = rtrim(rtrim(number_format((float) $used, 1), '0'), '.').' '.$unit;
        } else {
            $usedLabel = number_format((int) $used);
        }

        $limitLabel = $limit === null
            ? null
            : ($unit
                ? rtrim(rtrim(number_format((float) $limit, 1), '0'), '.').' '.$unit
                : number_format((int) $limit));

        return [
            'key' => $key,
            'label' => $label,
            'used' => $used,
            'limit' => $limit,
            'used_label' => $usedLabel,
            'limit_label' => $limitLabel,
            'unit' => $unit,
            'percent' => $percent,
        ];
    }

    /**
     * @param  array{integrations: list<array{label: string, status: string, attention_message: string|null}>}  $snapshot
     * @return list<array{severity: 'critical'|'warning'|'info', message: string}>
     */
    private function attentionItems(Tenant $tenant, array $snapshot): array
    {
        $items = [];

        if ($tenant->status?->value === 'suspended') {
            $items[] = [
                'severity' => 'warning',
                'message' => __('This channel partner is suspended.'),
            ];
        }

        foreach ($snapshot['integrations'] as $integration) {
            if (in_array($integration['status'], ['failed', 'needs_attention'], true)) {
                $items[] = [
                    'severity' => 'warning',
                    'message' => __(':name connection needs attention', ['name' => $integration['label']]),
                ];
            }
        }

        return $items;
    }

    /**
     * @param  array{activity_events: list<array{group_label: string, time: string, description: string, category: string, sort: int}>}  $snapshot
     * @return list<array{time: string, description: string, href: string|null}>
     */
    private function recentActivityEvents(Tenant $tenant, array $snapshot, int $limit): array
    {
        return collect($this->activityEvents($tenant, $snapshot))
            ->take($limit)
            ->map(fn (array $event): array => [
                'time' => $event['time'],
                'description' => $event['description'],
                'href' => null,
            ])
            ->all();
    }

    /**
     * @param  array{activity_events: list<array{group_label: string, time: string, description: string, category: string, sort: int}>}  $snapshot
     * @return list<array{group_label: string, time: string, description: string, category: string}>
     */
    private function activityEvents(Tenant $tenant, array $snapshot): array
    {
        $events = $snapshot['activity_events'];

        if ($tenant->created_at instanceof Carbon) {
            $createdAt = $tenant->created_at->timezone(config('app.timezone'));
            $events[] = [
                'group_label' => $this->activityGroupLabel($createdAt),
                'time' => $createdAt->format('g:i A'),
                'description' => __(':name onboarded', ['name' => $tenant->name]),
                'category' => 'system',
                'sort' => $createdAt->timestamp,
            ];
        }

        usort($events, fn (array $a, array $b): int => $b['sort'] <=> $a['sort']);

        return array_map(function (array $event): array {
            unset($event['sort']);

            return $event;
        }, $events);
    }

    private function activityGroupLabel(Carbon $at): string
    {
        if ($at->isToday()) {
            return __('Today');
        }

        if ($at->isYesterday()) {
            return __('Yesterday');
        }

        return $at->format('d M Y');
    }

    private function primaryAdminName(Tenant $tenant): ?string
    {
        try {
            return $tenant->run(function (): ?string {
                return User::query()
                    ->whereHas('role', fn ($query) => $query->where('slug', 'administrator'))
                    ->orderBy('id')
                    ->value('name');
            });
        } catch (Throwable) {
            return null;
        }
    }

    private function ratioLabel(int $used, ?int $limit): string
    {
        if ($limit === null) {
            return number_format($used).' / —';
        }

        return number_format($used).' / '.number_format($limit);
    }

    private function stringAttribute(Tenant $tenant, string $key): ?string
    {
        $value = $tenant->getAttribute($key);

        if ($value === null || $value === '') {
            return null;
        }

        return (string) $value;
    }
}
