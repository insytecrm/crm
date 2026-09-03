<?php

namespace App\Http\Controllers\Tenant;

use App\Actions\GenerateInvoicePdf;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\UpdateInvoiceRequest;
use App\Models\Booking;
use App\Support\DataTable\DataTableViewData;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class InvoiceController extends Controller
{
    public function index(Request $request): View
    {
        $invoices = Booking::query()
            ->whereNotNull('invoiced_at')
            ->with(['property', 'lead'])
            ->latest('invoice_date')
            ->latest('id')
            ->get();

        return view('tenant.invoices.index', array_merge([
            'invoices' => $invoices,
            'openModal' => old('_open_modal'),
        ], DataTableViewData::for($request->user(), 'invoices', $invoices)));
    }

    public function update(UpdateInvoiceRequest $request, Booking $booking): RedirectResponse
    {
        abort_unless($booking->hasInvoice(), 404);

        $booking->update($request->validated());

        return redirect()
            ->route('tenant.invoices.index')
            ->with('status', __('Invoice updated.'));
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
