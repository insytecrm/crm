<tr class="align-middle transition hover:bg-slate-50/60">
    <x-tenant.manageable-table.checkbox-cell :id="$payout->id" />
    <td x-show="isColumnVisible('property')" class="whitespace-nowrap px-4 py-3 align-middle text-sm font-medium text-black">
        <button
            type="button"
            class="text-start hover:underline"
            @click="$dispatch('open-modal', 'payout-{{ $payout->id }}')"
        >
            {{ $payout->property->project_name }}
        </button>
    </td>
    <td x-show="isColumnVisible('unit')" class="whitespace-nowrap px-4 py-3 align-middle text-sm text-slate-600">{{ $payout->unit_number }}</td>
    <td x-show="isColumnVisible('lead')" class="whitespace-nowrap px-4 py-3 align-middle text-sm text-slate-600">
        @if ($payout->lead)
            <x-tenant.lead-link :lead="$payout->lead" />
        @else
            —
        @endif
    </td>
    <td x-show="isColumnVisible('agreement_value')" class="whitespace-nowrap px-4 py-3 align-middle text-sm text-slate-600">₹{{ number_format($payout->agreement_value) }}</td>
    <td x-show="isColumnVisible('payout_amount')" class="whitespace-nowrap px-4 py-3 align-middle text-sm font-medium text-black">₹{{ number_format($payout->payout_amount ?? 0) }}</td>
    <td x-show="isColumnVisible('agreement_date')" class="whitespace-nowrap px-4 py-3 align-middle text-sm text-slate-600">{{ $payout->agreement_date?->format('M j, Y') }}</td>
    <td x-show="isColumnVisible('payout_status')" class="whitespace-nowrap px-4 py-3 align-middle text-sm">
        @if ($payout->payout_paid_at)
            <span class="inline-flex rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-semibold text-emerald-700">{{ __('Paid') }}</span>
        @else
            <span class="inline-flex rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-semibold text-amber-700">{{ __('Pending') }}</span>
        @endif
    </td>
    <x-tenant.manageable-table.custom-column-cells :record-id="$payout->id" />
    <td x-show="isColumnVisible('actions')" class="whitespace-nowrap px-4 py-3 align-middle text-end">
        @include('tenant.payouts.partials.payout-actions', ['payout' => $payout])
    </td>
</tr>
