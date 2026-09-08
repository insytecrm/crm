<?php

namespace App\Http\Controllers\Platform;

use App\Enums\BillingPaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\BillingPayment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BillingPaymentController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->string('search'));
        $status = $request->string('status')->toString() ?: 'all';

        $query = BillingPayment::query()
            ->with(['tenant', 'invoice', 'plan'])
            ->latest('payment_date')
            ->latest('id');

        if ($search !== '') {
            $query->where(function ($builder) use ($search): void {
                $builder
                    ->where('transaction_id', 'like', '%'.$search.'%')
                    ->orWhere('gateway_transaction_id', 'like', '%'.$search.'%')
                    ->orWhereHas('tenant', function ($tenantQuery) use ($search): void {
                        $tenantQuery
                            ->where('name', 'like', '%'.$search.'%')
                            ->orWhere('id', 'like', '%'.$search.'%');
                    });
            });
        }

        if ($status !== 'all' && BillingPaymentStatus::tryFrom($status) !== null) {
            $query->where('status', $status);
        }

        return view('platform.revenue.payments.index', [
            'payments' => $query->paginate(20)->withQueryString(),
            'filters' => [
                'search' => $search,
                'status' => $status,
            ],
            'statusOptions' => [
                ['value' => 'all', 'label' => __('All')],
                ['value' => BillingPaymentStatus::Paid->value, 'label' => __('Paid')],
                ['value' => BillingPaymentStatus::Pending->value, 'label' => __('Pending')],
                ['value' => BillingPaymentStatus::Failed->value, 'label' => __('Failed')],
                ['value' => BillingPaymentStatus::Refunded->value, 'label' => __('Refunded')],
            ],
        ]);
    }

    public function show(BillingPayment $payment): View
    {
        $payment->load(['tenant', 'invoice', 'plan', 'subscription']);

        return view('platform.revenue.payments.show', [
            'payment' => $payment,
            'timeline' => $this->timeline($payment),
        ]);
    }

    public function retry(BillingPayment $payment): RedirectResponse
    {
        $payment->update([
            'status' => BillingPaymentStatus::Pending,
            'retried_at' => now(),
            'failed_at' => null,
            'initiated_at' => now(),
        ]);

        if ($payment->invoice !== null) {
            $payment->invoice->update([
                'payment_initiated_at' => now(),
            ]);
        }

        return back()->with('status', __('Payment retry initiated.'));
    }

    public function remind(BillingPayment $payment): RedirectResponse
    {
        $payment->update([
            'reminder_sent_at' => now(),
        ]);

        return back()->with('status', __('Payment reminder recorded.'));
    }

    /**
     * @return list<array{label: string, at: string|null}>
     */
    private function timeline(BillingPayment $payment): array
    {
        $invoice = $payment->invoice;

        $steps = [
            ['label' => __('Invoice Created'), 'at' => $invoice?->issued_at],
            ['label' => __('Payment Initiated'), 'at' => $payment->initiated_at ?? $invoice?->payment_initiated_at],
        ];

        if ($payment->status === BillingPaymentStatus::Failed) {
            $steps[] = ['label' => __('Payment Failed'), 'at' => $payment->failed_at];
            $steps[] = ['label' => __('Retry / Reminder'), 'at' => $payment->retried_at ?? $payment->reminder_sent_at];
        } else {
            $steps[] = ['label' => __('Payment Received'), 'at' => $payment->payment_date ?? $invoice?->paid_at];
        }

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
