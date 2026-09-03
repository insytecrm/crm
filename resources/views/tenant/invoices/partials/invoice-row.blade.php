<tr class="align-middle transition hover:bg-slate-50/60">
    <x-tenant.manageable-table.checkbox-cell :id="$invoice->id" />
    <td x-show="isColumnVisible('invoice_number')" class="whitespace-nowrap px-4 py-3 align-middle text-sm font-medium text-black">{{ $invoice->invoice_number ?? \App\Models\Booking::invoiceNumberFor($invoice->id) }}</td>
    <td x-show="isColumnVisible('lead')" class="min-w-0 px-4 py-3 align-middle">
        @if ($invoice->lead)
            <x-tenant.lead-link :lead="$invoice->lead" />
        @else
            —
        @endif
    </td>
    <td x-show="isColumnVisible('property')" class="px-4 py-3 align-middle">
        @include('tenant.leads.partials.booked-property-card', ['booking' => $invoice])
    </td>
    <td x-show="isColumnVisible('unit')" class="whitespace-nowrap px-4 py-3 align-middle text-sm text-slate-600">{{ $invoice->unit_number }}</td>
    <td x-show="isColumnVisible('agreement_value')" class="whitespace-nowrap px-4 py-3 align-middle text-sm text-slate-600">₹{{ number_format($invoice->agreement_value) }}</td>
    <td x-show="isColumnVisible('invoice_amount')" class="whitespace-nowrap px-4 py-3 align-middle text-sm font-medium text-black">₹{{ number_format($invoice->payout_amount ?? 0) }}</td>
    <td x-show="isColumnVisible('agreement_date')" class="whitespace-nowrap px-4 py-3 align-middle text-sm text-slate-600">{{ $invoice->agreement_date?->format('M j, Y') }}</td>
    <td x-show="isColumnVisible('invoice_date')" class="whitespace-nowrap px-4 py-3 align-middle text-sm text-slate-600">{{ $invoice->invoice_date?->format('M j, Y') }}</td>
    <td x-show="isColumnVisible('payment_status')" class="whitespace-nowrap px-4 py-3 align-middle text-sm">
        @if ($invoice->payout_paid_at)
            <span class="inline-flex rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-semibold text-emerald-700">{{ __('Paid') }}</span>
        @else
            <span class="inline-flex rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-semibold text-amber-700">{{ __('Pending') }}</span>
        @endif
    </td>
    <x-tenant.manageable-table.custom-column-cells :record-id="$invoice->id" />
    <td x-show="isColumnVisible('actions')" class="whitespace-nowrap px-4 py-3 align-middle text-end">
        @include('tenant.invoices.partials.invoice-actions', ['invoice' => $invoice])
    </td>
</tr>
