<?php

namespace App\Queries;

use App\Enums\BillingInvoiceStatus;
use App\Enums\QuotationStatus;
use App\Models\BillingInvoice;
use App\Models\PartnerSubscription;
use App\Models\Quotation;
use App\Support\Platform\BillingMoney;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class PlatformDashboardMyDay
{
    /**
     * @return Collection<int, array{
     *     key: string,
     *     kind: 'trial'|'subscription'|'quotation'|'invoice',
     *     severity: 'critical'|'warning'|'info',
     *     label: string,
     *     meta: string,
     *     href: string,
     *     sort_at: Carbon
     * }>
     */
    public function items(int $limit = 20): Collection
    {
        $items = collect()
            ->merge($this->unpaidInvoiceItems())
            ->merge($this->draftQuotationItems())
            ->merge($this->trialEndingItems())
            ->merge($this->subscriptionEndingItems());

        return $items
            ->sortBy([
                fn (array $item): int => match ($item['severity']) {
                    'critical' => 0,
                    'warning' => 1,
                    default => 2,
                },
                ['sort_at', 'asc'],
            ])
            ->take($limit)
            ->values();
    }

    /**
     * @return array{
     *     trials: int,
     *     subscriptions: int,
     *     draft_quotations: int,
     *     unpaid_invoices: int
     * }
     */
    public function counts(): array
    {
        return [
            'trials' => PartnerSubscription::query()->trialsEndingSoon()->count(),
            'subscriptions' => PartnerSubscription::query()->expiringSoon()->count(),
            'draft_quotations' => Quotation::query()->where('status', QuotationStatus::Draft)->count(),
            'unpaid_invoices' => BillingInvoice::query()
                ->whereIn('status', [BillingInvoiceStatus::Pending, BillingInvoiceStatus::Overdue])
                ->count(),
        ];
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function unpaidInvoiceItems(): Collection
    {
        return BillingInvoice::query()
            ->with('tenant')
            ->whereIn('status', [BillingInvoiceStatus::Pending, BillingInvoiceStatus::Overdue])
            ->orderBy('due_at')
            ->limit(8)
            ->get()
            ->map(function (BillingInvoice $invoice): array {
                $partner = $invoice->tenant?->name ?? $invoice->billed_to_name ?? __('Unknown partner');

                return [
                    'key' => 'invoice-'.$invoice->id,
                    'kind' => 'invoice',
                    'severity' => $invoice->status === BillingInvoiceStatus::Overdue ? 'critical' : 'warning',
                    'label' => __('Unpaid invoice :number for :partner', [
                        'number' => '#'.$invoice->number,
                        'partner' => $partner,
                    ]),
                    'meta' => __('Due :date · :amount', [
                        'date' => $invoice->due_at?->timezone(config('app.timezone'))->format('d M Y') ?? '—',
                        'amount' => BillingMoney::format((int) $invoice->total),
                    ]),
                    'href' => route('platform.revenue.invoices.show', $invoice),
                    'sort_at' => $invoice->due_at ?? $invoice->issued_at ?? now(),
                ];
            });
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function draftQuotationItems(): Collection
    {
        return Quotation::query()
            ->orderBy('valid_until')
            ->where('status', QuotationStatus::Draft)
            ->limit(6)
            ->get()
            ->map(function (Quotation $quotation): array {
                return [
                    'key' => 'quotation-'.$quotation->id,
                    'kind' => 'quotation',
                    'severity' => 'info',
                    'label' => __('Draft quotation :number for :company', [
                        'number' => '#'.$quotation->number,
                        'company' => $quotation->companyDisplayName(),
                    ]),
                    'meta' => __('Valid until :date · :amount', [
                        'date' => $quotation->valid_until?->timezone(config('app.timezone'))->format('d M Y') ?? '—',
                        'amount' => $quotation->amountLabel(),
                    ]),
                    'href' => route('platform.quotations.show', $quotation),
                    'sort_at' => $quotation->valid_until ?? $quotation->created_at ?? now(),
                ];
            });
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function trialEndingItems(): Collection
    {
        return PartnerSubscription::query()
            ->with('tenant')
            ->trialsEndingSoon()
            ->orderBy('trial_ends_at')
            ->limit(6)
            ->get()
            ->map(function (PartnerSubscription $subscription): array {
                return [
                    'key' => 'trial-'.$subscription->id,
                    'kind' => 'trial',
                    'severity' => 'warning',
                    'label' => __('Trial ending for :partner', [
                        'partner' => $subscription->tenant?->name ?? __('Unknown partner'),
                    ]),
                    'meta' => __('Ends :date', [
                        'date' => $subscription->trial_ends_at?->timezone(config('app.timezone'))->format('d M Y') ?? '—',
                    ]),
                    'href' => route('platform.revenue.subscriptions.show', $subscription),
                    'sort_at' => $subscription->trial_ends_at ?? now(),
                ];
            });
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function subscriptionEndingItems(): Collection
    {
        return PartnerSubscription::query()
            ->with('tenant')
            ->expiringSoon()
            ->orderBy('next_billing_at')
            ->limit(6)
            ->get()
            ->map(function (PartnerSubscription $subscription): array {
                return [
                    'key' => 'subscription-'.$subscription->id,
                    'kind' => 'subscription',
                    'severity' => 'warning',
                    'label' => __('Subscription renewal for :partner', [
                        'partner' => $subscription->tenant?->name ?? __('Unknown partner'),
                    ]),
                    'meta' => __('Renews :date', [
                        'date' => $subscription->next_billing_at?->timezone(config('app.timezone'))->format('d M Y') ?? '—',
                    ]),
                    'href' => route('platform.revenue.subscriptions.show', $subscription),
                    'sort_at' => $subscription->next_billing_at ?? now(),
                ];
            });
    }
}
