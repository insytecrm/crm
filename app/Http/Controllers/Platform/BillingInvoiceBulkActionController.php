<?php

namespace App\Http\Controllers\Platform;

use App\Actions\MarkBillingInvoicePaid;
use App\Enums\BillingInvoiceStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Platform\BulkBillingInvoiceActionRequest;
use App\Models\BillingInvoice;
use Illuminate\Http\RedirectResponse;

class BillingInvoiceBulkActionController extends Controller
{
    public function markPaid(
        BulkBillingInvoiceActionRequest $request,
        MarkBillingInvoicePaid $markBillingInvoicePaid,
    ): RedirectResponse {
        $invoices = BillingInvoice::query()
            ->whereIn('id', $request->validated('ids'))
            ->whereIn('status', [BillingInvoiceStatus::Pending, BillingInvoiceStatus::Overdue])
            ->get();

        foreach ($invoices as $invoice) {
            $markBillingInvoicePaid->handle($invoice);
        }

        return redirect()
            ->route('platform.revenue.invoices', $request->input('_redirect_query', []))
            ->with('status', trans_choice('Marked :count invoice as paid.|Marked :count invoices as paid.', $invoices->count(), ['count' => $invoices->count()]));
    }

    public function destroy(BulkBillingInvoiceActionRequest $request): RedirectResponse
    {
        $cancelled = BillingInvoice::query()
            ->whereIn('id', $request->validated('ids'))
            ->whereNot('status', BillingInvoiceStatus::Paid)
            ->update([
                'status' => BillingInvoiceStatus::Cancelled,
            ]);

        return redirect()
            ->route('platform.revenue.invoices', $request->input('_redirect_query', []))
            ->with('status', trans_choice('Cancelled :count invoice.|Cancelled :count invoices.', $cancelled, ['count' => $cancelled]));
    }
}
