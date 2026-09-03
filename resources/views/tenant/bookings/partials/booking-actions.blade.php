@props(['booking'])

<x-ui.action-icon-group {{ $attributes }}>
    <x-ui.action-icon
        icon="view"
        type="button"
        :title="__('View Details')"
        @click="$dispatch('open-modal', 'booking-{{ $booking->id }}')"
    />

    @if ($booking->canMarkAgreement())
        <x-ui.action-icon
            icon="agreement"
            type="button"
            :title="__('Mark Agreement')"
            @click="$dispatch('open-modal', 'mark-agreement-{{ $booking->id }}')"
        />
    @endif

    @if ($booking->canCreateInvoice())
        <x-ui.action-icon
            icon="invoice"
            type="button"
            :title="__('Create Invoice')"
            @click="$dispatch('open-modal', 'create-invoice-{{ $booking->id }}')"
        />
    @endif
</x-ui.action-icon-group>
