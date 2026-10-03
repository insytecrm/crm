<?php

namespace App\Support\Platform;

use App\Enums\BillingCycle;
use App\Enums\BillingInvoiceStatus;
use App\Enums\BillingPaymentStatus;
use App\Enums\SubscriptionStatus;
use App\Models\BillingInvoice;
use App\Models\BillingPayment;
use App\Models\PartnerSubscription;
use App\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class BillingOverviewData
{
    /**
     * @return array{
     *     period: array{key: string, label: string, start: string, end: string},
     *     chart_window: string,
     *     snapshot: array{
     *         total_revenue: string,
     *         collected: string,
     *         pending: string,
     *         overdue: string,
     *         comparison: string|null,
     *         comparison_direction: 'up'|'down'|'flat'
     *     },
     *     chart: list<array{label: string, revenue: int, collected: int, pending: int}>,
     *     by_plan: list<array{plan: string, partners: int, revenue: string}>,
     *     by_cycle: array{monthly: string, annual: string},
     *     attention_summary: list<array{title: string, detail: string}>,
     *     attention_rows: list<array{
     *         partner: string,
     *         amount: string,
     *         issue: string,
     *         date: string,
     *         actions: list<array{label: string, href: string|null, method: string|null}>
     *     }>,
     *     recent_payments: list<array{
     *         partner: string,
     *         amount: string,
     *         type: string,
     *         date: string,
     *         status: string,
     *         status_label: string,
     *         href: string
     *     }>
     * }
     */
    public function forRequest(Request $request): array
    {
        $period = BillingPeriod::fromRequest(
            $request->string('range')->toString() ?: null,
            $request->string('from')->toString() ?: null,
            $request->string('to')->toString() ?: null,
        );
        $chartWindow = BillingPeriod::chartWindow($request->string('chart')->toString() ?: null);

        $totalRevenue = $this->invoiceTotalBetween($period['start'], $period['end']);
        $previousRevenue = $this->invoiceTotalBetween($period['previous_start'], $period['previous_end']);
        $collected = $this->paymentSumBetween($period['start'], $period['end'], BillingPaymentStatus::Paid);
        $pending = $this->invoiceSumByStatuses($period['start'], $period['end'], [BillingInvoiceStatus::Pending]);
        $overdue = $this->invoiceSumByStatuses($period['start'], $period['end'], [BillingInvoiceStatus::Overdue]);

        return [
            'period' => [
                'key' => $period['key'],
                'label' => $period['label'],
                'start' => $period['start']->toDateString(),
                'end' => $period['end']->toDateString(),
            ],
            'chart_window' => $chartWindow['key'],
            'snapshot' => [
                'total_revenue' => BillingMoney::format($totalRevenue),
                'collected' => BillingMoney::format($collected),
                'pending' => BillingMoney::format($pending),
                'overdue' => BillingMoney::format($overdue),
                ...$this->comparisonMeta($totalRevenue, $previousRevenue),
            ],
            'chart' => $this->chartSeries($chartWindow['start'], $chartWindow['end'], $chartWindow['key']),
            'by_plan' => $this->revenueByPlan($period['start'], $period['end']),
            'by_cycle' => [
                'monthly' => BillingMoney::format($this->revenueByCycle($period['start'], $period['end'], BillingCycle::Monthly)),
                'annual' => BillingMoney::format($this->revenueByCycle($period['start'], $period['end'], BillingCycle::Annual)),
            ],
            'attention_summary' => $this->attentionSummary(),
            'attention_rows' => $this->attentionRows(),
            'recent_payments' => $this->recentPayments(),
        ];
    }

    private function invoiceTotalBetween(Carbon $start, Carbon $end): int
    {
        return (int) BillingInvoice::query()
            ->whereBetween('issued_at', [$start, $end])
            ->where('status', '!=', BillingInvoiceStatus::Cancelled)
            ->sum('total');
    }

    /**
     * @param  list<BillingInvoiceStatus>  $statuses
     */
    private function invoiceSumByStatuses(Carbon $start, Carbon $end, array $statuses): int
    {
        return (int) BillingInvoice::query()
            ->whereBetween('issued_at', [$start, $end])
            ->whereIn('status', $statuses)
            ->sum('total');
    }

    private function paymentSumBetween(Carbon $start, Carbon $end, BillingPaymentStatus $status): int
    {
        return (int) BillingPayment::query()
            ->where('status', $status)
            ->whereBetween('payment_date', [$start, $end])
            ->sum('amount');
    }

    private function revenueByCycle(Carbon $start, Carbon $end, BillingCycle $cycle): int
    {
        return (int) BillingInvoice::query()
            ->whereBetween('issued_at', [$start, $end])
            ->where('billing_cycle', $cycle)
            ->where('status', '!=', BillingInvoiceStatus::Cancelled)
            ->sum('total');
    }

    /**
     * @return list<array{plan: string, partners: int, revenue: string}>
     */
    private function revenueByPlan(Carbon $start, Carbon $end): array
    {
        $revenueByPlanId = BillingInvoice::query()
            ->selectRaw('plan_id, SUM(total) as revenue_total')
            ->whereBetween('issued_at', [$start, $end])
            ->where('status', '!=', BillingInvoiceStatus::Cancelled)
            ->whereNotNull('plan_id')
            ->groupBy('plan_id')
            ->pluck('revenue_total', 'plan_id');

        if ($revenueByPlanId->isEmpty()) {
            return [];
        }

        $partnerCounts = PartnerSubscription::query()
            ->selectRaw('plan_id, COUNT(*) as partners_count')
            ->whereIn('status', [SubscriptionStatus::Active, SubscriptionStatus::Trial, SubscriptionStatus::PastDue])
            ->whereIn('plan_id', $revenueByPlanId->keys())
            ->groupBy('plan_id')
            ->pluck('partners_count', 'plan_id');

        return Plan::query()
            ->whereIn('id', $revenueByPlanId->keys())
            ->orderBy('name')
            ->get()
            ->map(fn (Plan $plan): array => [
                'plan' => $plan->name,
                'partners' => (int) ($partnerCounts[$plan->id] ?? 0),
                'revenue' => BillingMoney::format((int) ($revenueByPlanId[$plan->id] ?? 0)),
            ])
            ->all();
    }

    /**
     * @return list<array{label: string, revenue: int, collected: int, pending: int}>
     */
    private function chartSeries(Carbon $start, Carbon $end, string $window): array
    {
        $points = collect();

        if (in_array($window, ['3_months', '12_months'], true)) {
            $cursor = $start->copy()->startOfMonth();
            while ($cursor->lte($end)) {
                $bucketStart = $cursor->copy()->startOfMonth();
                $bucketEnd = $cursor->copy()->endOfMonth();
                $points->push([
                    'label' => $bucketStart->format('M'),
                    'revenue' => $this->invoiceTotalBetween($bucketStart, $bucketEnd),
                    'collected' => $this->paymentSumBetween($bucketStart, $bucketEnd, BillingPaymentStatus::Paid),
                    'pending' => $this->invoiceSumByStatuses($bucketStart, $bucketEnd, [
                        BillingInvoiceStatus::Pending,
                        BillingInvoiceStatus::Overdue,
                    ]),
                ]);
                $cursor->addMonthNoOverflow();
            }

            return $points->all();
        }

        $cursor = $start->copy()->startOfDay();
        while ($cursor->lte($end)) {
            $bucketStart = $cursor->copy()->startOfDay();
            $bucketEnd = $cursor->copy()->endOfDay();
            $points->push([
                'label' => $bucketStart->format('d M'),
                'revenue' => $this->invoiceTotalBetween($bucketStart, $bucketEnd),
                'collected' => $this->paymentSumBetween($bucketStart, $bucketEnd, BillingPaymentStatus::Paid),
                'pending' => $this->invoiceSumByStatuses($bucketStart, $bucketEnd, [
                    BillingInvoiceStatus::Pending,
                    BillingInvoiceStatus::Overdue,
                ]),
            ]);
            $cursor->addDay();
        }

        return $points->all();
    }

    /**
     * @return list<array{title: string, detail: string}>
     */
    private function attentionSummary(): array
    {
        $overdueCount = BillingInvoice::query()->where('status', BillingInvoiceStatus::Overdue)->count();
        $overdueAmount = (int) BillingInvoice::query()->where('status', BillingInvoiceStatus::Overdue)->sum('total');
        $failedCount = BillingPayment::query()->where('status', BillingPaymentStatus::Failed)->count();
        $expiringCount = PartnerSubscription::query()->expiringSoon()->count();

        $items = [];

        if ($overdueCount > 0) {
            $items[] = [
                'title' => trans_choice(':count payment overdue|:count payments overdue', $overdueCount, ['count' => $overdueCount]),
                'detail' => __(':amount pending', ['amount' => BillingMoney::format($overdueAmount)]),
            ];
        }

        if ($failedCount > 0) {
            $items[] = [
                'title' => trans_choice(':count payment failure|:count payment failures', $failedCount, ['count' => $failedCount]),
                'detail' => __('Needs retry/payment update'),
            ];
        }

        if ($expiringCount > 0) {
            $soonest = PartnerSubscription::query()->expiringSoon()->orderBy('next_billing_at')->first();
            $days = $soonest?->next_billing_at?->diffInDays(now()) ?? 0;
            $items[] = [
                'title' => trans_choice(':count subscription expiring|:count subscriptions expiring', $expiringCount, ['count' => $expiringCount]),
                'detail' => __('Renewal in :days days', ['days' => max((int) $days, 0)]),
            ];
        }

        return $items;
    }

    /**
     * @return list<array{
     *     partner: string,
     *     amount: string,
     *     issue: string,
     *     date: string,
     *     actions: list<array{label: string, href: string|null, method: string|null}>
     * }>
     */
    private function attentionRows(): array
    {
        $rows = collect();

        BillingInvoice::query()
            ->with('tenant')
            ->where('status', BillingInvoiceStatus::Overdue)
            ->latest('due_at')
            ->limit(10)
            ->get()
            ->each(function (BillingInvoice $invoice) use ($rows): void {
                $rows->push([
                    'partner' => $invoice->tenant?->name ?? __('Unknown partner'),
                    'amount' => BillingMoney::format((int) $invoice->total),
                    'issue' => __('Overdue'),
                    'date' => $invoice->due_at?->timezone(config('app.timezone'))->format('d M Y') ?? '—',
                    'actions' => [
                        ['label' => __('View'), 'href' => route('platform.revenue.invoices.show', $invoice), 'method' => null],
                        ['label' => __('Send Reminder'), 'href' => route('platform.revenue.invoices.send', $invoice), 'method' => 'POST'],
                        ['label' => __('View Invoice'), 'href' => route('platform.revenue.invoices.show', $invoice), 'method' => null],
                    ],
                ]);
            });

        BillingPayment::query()
            ->with(['tenant', 'invoice'])
            ->where('status', BillingPaymentStatus::Failed)
            ->latest('failed_at')
            ->limit(10)
            ->get()
            ->each(function (BillingPayment $payment) use ($rows): void {
                $rows->push([
                    'partner' => $payment->tenant?->name ?? __('Unknown partner'),
                    'amount' => BillingMoney::format((int) $payment->amount),
                    'issue' => __('Payment failed'),
                    'date' => ($payment->failed_at ?? $payment->payment_date)?->timezone(config('app.timezone'))->format('d M Y') ?? '—',
                    'actions' => [
                        ['label' => __('View'), 'href' => route('platform.revenue.payments.show', $payment), 'method' => null],
                        ['label' => __('Retry Payment'), 'href' => route('platform.revenue.payments.retry', $payment), 'method' => 'POST'],
                        ...($payment->invoice
                            ? [['label' => __('View Invoice'), 'href' => route('platform.revenue.invoices.show', $payment->invoice), 'method' => null]]
                            : []),
                    ],
                ]);
            });

        PartnerSubscription::query()
            ->with('tenant')
            ->expiringSoon()
            ->orderBy('next_billing_at')
            ->limit(10)
            ->get()
            ->each(function (PartnerSubscription $subscription) use ($rows): void {
                $rows->push([
                    'partner' => $subscription->tenant?->name ?? __('Unknown partner'),
                    'amount' => BillingMoney::format((int) $subscription->amount),
                    'issue' => __('Subscription expiring'),
                    'date' => $subscription->next_billing_at?->timezone(config('app.timezone'))->format('d M Y') ?? '—',
                    'actions' => [
                        ['label' => __('View'), 'href' => route('platform.revenue.subscriptions.show', $subscription), 'method' => null],
                    ],
                ]);
            });

        return $rows->take(15)->values()->all();
    }

    /**
     * @return list<array{
     *     partner: string,
     *     amount: string,
     *     type: string,
     *     date: string,
     *     status: string,
     *     status_label: string,
     *     href: string
     * }>
     */
    private function recentPayments(): array
    {
        return BillingPayment::query()
            ->with('tenant')
            ->latest('payment_date')
            ->latest('id')
            ->limit(8)
            ->get()
            ->map(fn (BillingPayment $payment): array => [
                'partner' => $payment->tenant?->name ?? __('Unknown partner'),
                'amount' => BillingMoney::format((int) $payment->amount),
                'type' => $payment->type->label(),
                'date' => ($payment->payment_date ?? $payment->created_at)?->timezone(config('app.timezone'))->format('d M Y') ?? '—',
                'status' => $payment->status->value,
                'status_label' => $payment->status->label(),
                'href' => route('platform.revenue.payments.show', $payment),
            ])
            ->all();
    }

    /**
     * @return array{comparison: string|null, comparison_direction: 'up'|'down'|'flat'}
     */
    private function comparisonMeta(int $current, int $previous): array
    {
        if ($current === 0 && $previous === 0) {
            return [
                'comparison' => null,
                'comparison_direction' => 'flat',
            ];
        }

        if ($previous === 0) {
            return [
                'comparison' => __('New vs previous period'),
                'comparison_direction' => 'up',
            ];
        }

        $delta = $current - $previous;
        $percent = round((abs($delta) / $previous) * 100, 1);

        if ($delta === 0) {
            return [
                'comparison' => __('No change vs previous period'),
                'comparison_direction' => 'flat',
            ];
        }

        $direction = $delta > 0 ? 'up' : 'down';
        $arrow = $delta > 0 ? '↑' : '↓';

        return [
            'comparison' => __(':arrow :percent% vs previous period', [
                'arrow' => $arrow,
                'percent' => $percent,
            ]),
            'comparison_direction' => $direction,
        ];
    }
}
