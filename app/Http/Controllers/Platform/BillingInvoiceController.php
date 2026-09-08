<?php

namespace App\Http\Controllers\Platform;

use App\Actions\MarkBillingInvoicePaid;
use App\Enums\BillingInvoiceStatus;
use App\Http\Controllers\Controller;
use App\Models\BillingInvoice;
use App\Support\Platform\BillingMoney;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class BillingInvoiceController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->string('search'));
        $status = $request->string('status')->toString() ?: 'all';

        $query = BillingInvoice::query()
            ->with(['tenant', 'plan'])
            ->latest('issued_at')
            ->latest('id');

        if ($search !== '') {
            $query->where(function ($builder) use ($search): void {
                $builder
                    ->where('number', 'like', '%'.$search.'%')
                    ->orWhere('billed_to_name', 'like', '%'.$search.'%')
                    ->orWhereHas('tenant', function ($tenantQuery) use ($search): void {
                        $tenantQuery
                            ->where('name', 'like', '%'.$search.'%')
                            ->orWhere('id', 'like', '%'.$search.'%');
                    });
            });
        }

        if ($status !== 'all' && BillingInvoiceStatus::tryFrom($status) !== null) {
            $query->where('status', $status);
        }

        return view('platform.revenue.invoices.index', [
            'invoices' => $query->paginate(20)->withQueryString(),
            'filters' => [
                'search' => $search,
                'status' => $status,
            ],
            'statusOptions' => [
                ['value' => 'all', 'label' => __('All')],
                ['value' => BillingInvoiceStatus::Paid->value, 'label' => __('Paid')],
                ['value' => BillingInvoiceStatus::Pending->value, 'label' => __('Pending')],
                ['value' => BillingInvoiceStatus::Overdue->value, 'label' => __('Overdue')],
                ['value' => BillingInvoiceStatus::Cancelled->value, 'label' => __('Cancelled')],
            ],
        ]);
    }

    public function show(BillingInvoice $invoice): View
    {
        $invoice->load(['tenant', 'plan', 'subscription', 'payments']);

        $latestPayment = $invoice->payments()->latest('payment_date')->latest('id')->first();

        return view('platform.revenue.invoices.show', [
            'invoice' => $invoice,
            'payment' => $latestPayment,
            'timeline' => $this->timeline($invoice, $latestPayment),
        ]);
    }

    public function download(BillingInvoice $invoice): Response
    {
        $invoice->load(['tenant', 'plan']);

        $lines = [
            __('Invoice').': #'.$invoice->number,
            __('Channel Partner').': '.($invoice->tenant?->name ?? $invoice->billed_to_name ?? '—'),
            __('Issue Date').': '.($invoice->issued_at?->timezone(config('app.timezone'))->format('d M Y') ?? '—'),
            __('Due Date').': '.($invoice->due_at?->timezone(config('app.timezone'))->format('d M Y') ?? '—'),
            __('Status').': '.$invoice->status->label(),
            __('Plan').': '.($invoice->plan?->name ?? '—'),
            __('Subtotal').': '.BillingMoney::format((int) $invoice->subtotal),
            __('Discount').': '.BillingMoney::format((int) $invoice->discount_amount),
            __('Tax').': '.BillingMoney::format((int) $invoice->tax_amount),
            __('Total').': '.BillingMoney::format((int) $invoice->total),
        ];

        return response(implode(PHP_EOL, $lines).PHP_EOL, 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$invoice->number.'.txt"',
        ]);
    }

    public function send(BillingInvoice $invoice): RedirectResponse
    {
        $invoice->update([
            'sent_at' => $invoice->sent_at ?? now(),
        ]);

        return back()->with('status', __('Invoice marked as sent.'));
    }

    public function markPaid(BillingInvoice $invoice, MarkBillingInvoicePaid $action): RedirectResponse
    {
        $action->handle($invoice);

        return back()->with('status', __('Invoice marked as paid.'));
    }

    /**
     * @return list<array{label: string, at: string|null}>
     */
    private function timeline(BillingInvoice $invoice, mixed $payment): array
    {
        $steps = [
            ['label' => __('Invoice Created'), 'at' => $invoice->issued_at],
            ['label' => __('Invoice Sent'), 'at' => $invoice->sent_at],
            ['label' => __('Payment Initiated'), 'at' => $invoice->payment_initiated_at ?? $payment?->initiated_at],
            ['label' => __('Payment Received'), 'at' => $invoice->paid_at ?? $payment?->payment_date],
        ];

        return collect($steps)
            ->filter(fn (array $step): bool => $step['at'] !== null)
            ->map(fn (array $step): array => [
                'label' => $step['label'],
                'at' => $step['at']->timezone(config('app.timezone'))->format('d M Y g:i A'),
            ])
            ->values()
            ->all();
    }
}
