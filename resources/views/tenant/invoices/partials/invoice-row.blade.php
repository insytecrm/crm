<tr class="align-middle transition hover:bg-slate-50/60">
    <x-tenant.manageable-table.checkbox-cell :id="$invoice->id" />
    <td x-show="isColumnVisible('property')" class="whitespace-nowrap px-4 py-3 align-middle text-sm font-medium text-black">
        <button
            type="button"
            class="text-start hover:underline"
            @click="$dispatch('open-modal', 'invoice-{{ $invoice->id }}')"
        >
            {{ $invoice->property->project_name }}
        </button>
    </td>
    <td x-show="isColumnVisible('invoice_number')" class="whitespace-nowrap px-4 py-3 align-middle text-sm text-slate-600">{{ $invoice->invoice_number ?? \App\Models\Booking::invoiceNumberFor($invoice->id) }}</td>
    <td x-show="isColumnVisible('unit')" class="whitespace-nowrap px-4 py-3 align-middle text-sm text-slate-600">{{ $invoice->unit_number }}</td>
    <td x-show="isColumnVisible('lead')" class="whitespace-nowrap px-4 py-3 align-middle text-sm text-slate-600">
        @if ($invoice->lead)
            <x-tenant.lead-link :lead="$invoice->lead" />
        @else
            —
        @endif
    </td>
    <td x-show="isColumnVisible('invoice_amount')" class="whitespace-nowrap px-4 py-3 align-middle text-sm font-medium text-black">₹{{ number_format($invoice->payout_amount ?? 0) }}</td>
    <td x-show="isColumnVisible('invoice_date')" class="whitespace-nowrap px-4 py-3 align-middle text-sm text-slate-600">{{ $invoice->invoice_date?->format('M j, Y') }}</td>
    <td x-show="isColumnVisible('agreement_date')" class="whitespace-nowrap px-4 py-3 align-middle text-sm text-slate-600">{{ $invoice->agreement_date?->format('M j, Y') }}</td>
    <x-tenant.manageable-table.custom-column-cells :record-id="$invoice->id" />
    <td x-show="isColumnVisible('actions')" class="whitespace-nowrap px-4 py-3 align-middle text-end">
        @include('tenant.invoices.partials.invoice-actions', ['invoice' => $invoice])
    </td>
</tr>
