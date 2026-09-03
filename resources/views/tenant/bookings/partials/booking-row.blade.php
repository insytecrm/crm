<tr class="align-middle transition hover:bg-slate-50/60">
    <x-tenant.manageable-table.checkbox-cell :id="$booking->id" />
    <td x-show="isColumnVisible('property')" class="whitespace-nowrap px-4 py-3 align-middle text-sm font-medium text-black">
        <button
            type="button"
            class="text-start hover:underline"
            @click="$dispatch('open-modal', 'booking-{{ $booking->id }}')"
        >
            {{ $booking->property->project_name }}
        </button>
    </td>
    <td x-show="isColumnVisible('configuration')" class="whitespace-nowrap px-4 py-3 align-middle text-sm text-slate-600">{{ $booking->configuration_name }}</td>
    <td x-show="isColumnVisible('unit')" class="whitespace-nowrap px-4 py-3 align-middle text-sm text-slate-600">{{ $booking->unit_number }}</td>
    <td x-show="isColumnVisible('agreement_value')" class="whitespace-nowrap px-4 py-3 align-middle text-sm text-slate-600">₹{{ number_format($booking->agreement_value) }}</td>
    <td x-show="isColumnVisible('booking_date')" class="whitespace-nowrap px-4 py-3 align-middle text-sm text-slate-600">{{ $booking->booking_date->format('M j, Y') }}</td>
    <td x-show="isColumnVisible('lead')" class="whitespace-nowrap px-4 py-3 align-middle text-sm text-slate-600">
        @if ($booking->lead)
            <x-tenant.lead-link :lead="$booking->lead" />
        @else
            —
        @endif
    </td>
    <td x-show="isColumnVisible('payout_amount')" class="whitespace-nowrap px-4 py-3 align-middle text-sm text-slate-600">
        @if ($booking->payout_amount)
            ₹{{ number_format($booking->payout_amount) }}
        @else
            —
        @endif
    </td>
    <x-tenant.manageable-table.custom-column-cells :record-id="$booking->id" />
    <td x-show="isColumnVisible('actions')" class="whitespace-nowrap px-4 py-3 align-middle text-end">
        @include('tenant.bookings.partials.booking-actions', ['booking' => $booking])
    </td>
</tr>
