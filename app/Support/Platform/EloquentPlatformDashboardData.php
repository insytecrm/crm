<?php

namespace App\Support\Platform;

use App\Contracts\PlatformDashboardData;
use App\Enums\BillingInvoiceStatus;
use App\Enums\BillingPaymentStatus;
use App\Enums\SubscriptionStatus;
use App\Enums\TenantStatus;
use App\Models\BillingInvoice;
use App\Models\BillingPayment;
use App\Models\PartnerSubscription;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Carbon;
use Throwable;

class EloquentPlatformDashboardData implements PlatformDashboardData
{
    public function get(): array
    {
        $now = now();
        $endOfLastMonth = $now->copy()->subMonthNoOverflow()->endOfMonth();

        $total = Tenant::query()->count();
        $active = Tenant::query()->where('status', TenantStatus::Active)->count();
        $suspended = Tenant::query()->where('status', TenantStatus::Suspended)->count();
        $totalLastMonthEnd = Tenant::query()
            ->where('created_at', '<=', $endOfLastMonth)
            ->count();

        $userCounts = $this->activeUserCounts($endOfLastMonth);
        $monthStart = $now->copy()->startOfMonth();
        $monthEnd = $now->copy()->endOfMonth();
        $previousMonthStart = $now->copy()->subMonthNoOverflow()->startOfMonth();
        $previousMonthEnd = $now->copy()->subMonthNoOverflow()->endOfMonth();

        $monthlyRevenue = (int) BillingInvoice::query()
            ->whereBetween('issued_at', [$monthStart, $monthEnd])
            ->where('status', '!=', BillingInvoiceStatus::Cancelled)
            ->sum('total');
        $previousMonthlyRevenue = (int) BillingInvoice::query()
            ->whereBetween('issued_at', [$previousMonthStart, $previousMonthEnd])
            ->where('status', '!=', BillingInvoiceStatus::Cancelled)
            ->sum('total');
        $trialCount = PartnerSubscription::query()->where('status', SubscriptionStatus::Trial)->count();
        $pastDueCount = PartnerSubscription::query()->where('status', SubscriptionStatus::PastDue)->count();

        $recentPartners = Tenant::query()
            ->latest('created_at')
            ->limit(8)
            ->get(['id', 'name', 'status', 'created_at']);

        return [
            'kpis' => [
                [
                    'key' => 'partners',
                    'label' => __('Channel Partners'),
                    'value' => number_format($total),
                    ...$this->changeMeta($total, $totalLastMonthEnd, absolute: false),
                    'href' => route('tenants.index'),
                ],
                [
                    'key' => 'revenue',
                    'label' => __('Monthly Revenue'),
                    'value' => $monthlyRevenue > 0 ? BillingMoney::format($monthlyRevenue) : '—',
                    ...($monthlyRevenue > 0 || $previousMonthlyRevenue > 0
                        ? $this->changeMeta($monthlyRevenue, $previousMonthlyRevenue, absolute: false)
                        : [
                            'change' => __('No billing data yet'),
                            'change_direction' => 'flat',
                        ]),
                    'href' => route('platform.revenue'),
                ],
                [
                    'key' => 'trials',
                    'label' => __('Active Trials'),
                    'value' => number_format($trialCount),
                    'change' => $trialCount > 0 ? __('From Revenue & Billing') : __('No trial data yet'),
                    'change_direction' => 'flat',
                    'href' => route('platform.revenue.subscriptions', ['status' => SubscriptionStatus::Trial->value]),
                ],
                [
                    'key' => 'users',
                    'label' => __('Active Users'),
                    'value' => number_format($userCounts['current']),
                    ...$this->changeMeta($userCounts['current'], $userCounts['previous'], absolute: false),
                    'href' => route('platform.analytics'),
                ],
            ],
            'attention' => $this->attentionItems($suspended, $pastDueCount),
            'revenue' => [
                'mrr' => $monthlyRevenue > 0 ? BillingMoney::format($monthlyRevenue) : '—',
                'arr' => $monthlyRevenue > 0 ? BillingMoney::format($monthlyRevenue * 12) : '—',
                'arpu' => ($monthlyRevenue > 0 && $active > 0)
                    ? BillingMoney::format((int) round($monthlyRevenue / $active))
                    : '—',
                'href' => route('platform.revenue'),
                'months' => $this->revenueMonths($now),
            ],
            'partners' => [
                'total' => $total,
                'href' => route('tenants.index'),
                'statuses' => [
                    [
                        'key' => 'active',
                        'label' => __('Active'),
                        'count' => $active,
                        'href' => route('tenants.index', ['status' => TenantStatus::Active->value]),
                    ],
                    [
                        'key' => 'trial',
                        'label' => __('Trial'),
                        'count' => $trialCount,
                        'href' => route('tenants.index', ['status' => 'trial']),
                    ],
                    [
                        'key' => 'past_due',
                        'label' => __('Past Due'),
                        'count' => $pastDueCount,
                        'href' => route('tenants.index', ['status' => 'past_due']),
                    ],
                    [
                        'key' => 'suspended',
                        'label' => __('Suspended'),
                        'count' => $suspended,
                        'href' => route('tenants.index', ['status' => TenantStatus::Suspended->value]),
                    ],
                ],
            ],
            'usage' => [
                'total_active' => 0,
                'change' => __('No usage data yet'),
                'change_direction' => 'flat',
                'href' => route('platform.integrations'),
                'integrations' => [],
            ],
            'activity' => $recentPartners
                ->map(function (Tenant $tenant): array {
                    /** @var Carbon $createdAt */
                    $createdAt = $tenant->created_at;

                    return [
                        'time' => $createdAt->timezone(config('app.timezone'))->format('g:i A'),
                        'description' => __(':name joined InSyte', ['name' => $tenant->name]),
                        'href' => route('tenants.show', $tenant),
                    ];
                })
                ->all(),
            'upcoming' => [
                'today' => [],
                'this_week' => [],
            ],
        ];
    }

    /**
     * @return array{current: int, previous: int}
     */
    private function activeUserCounts(Carbon $endOfLastMonth): array
    {
        $current = 0;
        $previous = 0;

        Tenant::query()
            ->orderBy('id')
            ->each(function (Tenant $tenant) use (&$current, &$previous, $endOfLastMonth): void {
                try {
                    $tenant->run(function () use (&$current, &$previous, $endOfLastMonth): void {
                        $current += User::query()
                            ->where('is_active', true)
                            ->count();

                        $previous += User::query()
                            ->where('is_active', true)
                            ->where('created_at', '<=', $endOfLastMonth)
                            ->count();
                    });
                } catch (Throwable) {
                    // Skip partners whose database is missing or unavailable.
                }
            });

        return [
            'current' => $current,
            'previous' => $previous,
        ];
    }

    /**
     * @return list<array{
     *     key: string,
     *     severity: 'critical'|'warning'|'info',
     *     title: string,
     *     summary: string,
     *     meta: string|null,
     *     action_label: string,
     *     href: string
     * }>
     */
    private function attentionItems(int $suspended, int $pastDueCount): array
    {
        $items = [];

        if ($pastDueCount > 0) {
            $items[] = [
                'key' => 'past_due_subscriptions',
                'severity' => 'critical',
                'title' => __('Past Due Subscriptions'),
                'summary' => trans_choice(':count subscription is past due|:count subscriptions are past due', $pastDueCount, ['count' => $pastDueCount]),
                'meta' => __('Review invoices and payment retries'),
                'action_label' => __('View billing'),
                'href' => route('platform.revenue.subscriptions', ['status' => SubscriptionStatus::PastDue->value]),
            ];
        }

        $failedPayments = BillingPayment::query()->where('status', BillingPaymentStatus::Failed)->count();

        if ($failedPayments > 0) {
            $items[] = [
                'key' => 'failed_payments',
                'severity' => 'warning',
                'title' => __('Failed Payments'),
                'summary' => trans_choice(':count payment failed|:count payments failed', $failedPayments, ['count' => $failedPayments]),
                'meta' => __('Retry or send a reminder'),
                'action_label' => __('View payments'),
                'href' => route('platform.revenue.payments', ['status' => BillingPaymentStatus::Failed->value]),
            ];
        }

        if ($suspended > 0) {
            $items[] = [
                'key' => 'suspended_partners',
                'severity' => 'warning',
                'title' => __('Suspended Partners'),
                'summary' => trans_choice(':count partner is suspended|:count partners are suspended', $suspended, ['count' => $suspended]),
                'meta' => __('Review access and continue or restore'),
                'action_label' => __('View suspended'),
                'href' => route('tenants.index', ['status' => TenantStatus::Suspended->value]),
            ];
        }

        return $items;
    }

    /**
     * @return list<array{label: string, revenue: int}>
     */
    private function revenueMonths(Carbon $now): array
    {
        $points = [];

        for ($i = 5; $i >= 0; $i--) {
            $month = $now->copy()->subMonthsNoOverflow($i);
            $start = $month->copy()->startOfMonth();
            $end = $month->copy()->endOfMonth();

            $points[] = [
                'label' => $month->format('M'),
                'revenue' => (int) BillingInvoice::query()
                    ->whereBetween('issued_at', [$start, $end])
                    ->where('status', '!=', BillingInvoiceStatus::Cancelled)
                    ->sum('total'),
            ];
        }

        return $points;
    }

    /**
     * @return array{change: string, change_direction: 'up'|'down'|'flat'}
     */
    private function changeMeta(int $current, int $previous, bool $absolute): array
    {
        $vsLabel = __('vs last month');

        if ($previous === 0 && $current === 0) {
            return [
                'change' => __('No change'),
                'change_direction' => 'flat',
            ];
        }

        if ($previous === 0) {
            return [
                'change' => $absolute
                    ? __('↑ :count :label', ['count' => number_format($current), 'label' => $vsLabel])
                    : __('New'),
                'change_direction' => 'up',
            ];
        }

        $delta = $current - $previous;

        if ($delta === 0) {
            return [
                'change' => __('No change'),
                'change_direction' => 'flat',
            ];
        }

        $direction = $delta > 0 ? 'up' : 'down';
        $arrow = $delta > 0 ? '↑' : '↓';

        if ($absolute) {
            return [
                'change' => __(':arrow :count :label', [
                    'arrow' => $arrow,
                    'count' => number_format(abs($delta)),
                    'label' => $vsLabel,
                ]),
                'change_direction' => $direction,
            ];
        }

        $percent = round((abs($delta) / $previous) * 100, 1);

        return [
            'change' => __(':arrow :percent% :label', [
                'arrow' => $arrow,
                'percent' => $percent,
                'label' => $vsLabel,
            ]),
            'change_direction' => $direction,
        ];
    }
}
