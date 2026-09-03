<?php

namespace App\Http\Controllers\Tenant;

use App\Actions\GenerateInvoicePdf;
use App\Actions\LogLeadBookingMilestone;
use App\Enums\InvoicePaymentFilter;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\StoreInvoiceRequest;
use App\Http\Requests\Tenant\UpdateInvoiceRequest;
use App\Models\Booking;
use App\Support\DataTable\DataTableViewData;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class InvoiceController extends Controller
{
    public function index(Request $request): View
    {
        $filter = InvoicePaymentFilter::fromRequest($request->string('payment')->toString());
        $search = $request->string('search')->trim()->toString();

        $invoices = Booking::query()
            ->whereNotNull('invoiced_at')
            ->when(
                $filter === InvoicePaymentFilter::Pending,
                fn (Builder $query): Builder => $query->whereNull('payout_paid_at'),
            )
            ->when(
                $filter === InvoicePaymentFilter::Paid,
                fn (Builder $query): Builder => $query->whereNotNull('payout_paid_at'),
            )
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->where('invoice_number', 'like', "%{$search}%")
                        ->orWhere('unit_number', 'like', "%{$search}%")
                        ->orWhereHas('lead', fn (Builder $leadQuery): Builder => $leadQuery->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('property', fn (Builder $propertyQuery): Builder => $propertyQuery->where('project_name', 'like', "%{$search}%"));
                });
            })
            ->with(['property', 'lead.assignedTo'])
            ->latest('invoice_date')
            ->latest('id')
            ->get();

        $billableBookings = Booking::query()
            ->whereNotNull('agreement_date')
            ->whereNull('invoiced_at')
            ->with(['property', 'lead'])
            ->latest('agreement_date')
            ->latest('id')
            ->get();

        return view('tenant.invoices.index', array_merge([
            'invoices' => $invoices,
            'filter' => $filter,
            'search' => $search,
            'billableBookings' => $billableBookings,
            'openModal' => old('_open_modal') ?? ($request->boolean('create') ? 'create-invoice' : null),
        ], DataTableViewData::for($request->user(), 'invoices', $invoices)));
    }

    public function store(StoreInvoiceRequest $request, LogLeadBookingMilestone $logLeadBookingMilestone): RedirectResponse
    {
        $booking = Booking::query()->findOrFail($request->integer('booking_id'));

        abort_unless($booking->canCreateInvoice(), 404);

        $booking->update([
            'invoice_date' => $request->validated('invoice_date'),
            'invoice_number' => Booking::invoiceNumberFor($booking->id),
            'invoiced_at' => now(),
        ]);

        $logLeadBookingMilestone->invoice($booking);

        return redirect()
            ->route('tenant.invoices.index')
            ->with('status', __('Invoice created.'));
    }

    public function update(UpdateInvoiceRequest $request, Booking $booking): RedirectResponse
    {
        abort_unless($booking->hasInvoice(), 404);

        $booking->update($request->validated());

        return redirect()
            ->route('tenant.invoices.index')
            ->with('status', __('Invoice updated.'));
    }

    public function markPaid(Booking $booking, LogLeadBookingMilestone $logLeadBookingMilestone): RedirectResponse
    {
        abort_unless($booking->canMarkPayoutPaid(), 404);

        $booking->update([
            'payout_paid_at' => now(),
        ]);

        $logLeadBookingMilestone->payoutReceived($booking);

        return redirect()
            ->route('tenant.invoices.index')
            ->with('status', __('Invoice marked as paid.'));
    }

    public function downloadPdf(Booking $booking, GenerateInvoicePdf $generateInvoicePdf): Response
    {
        abort_unless($booking->hasInvoice(), 404);

        $pdf = $generateInvoicePdf->handle($booking);

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$booking->pdfFilename().'"',
        ]);
    }
}
