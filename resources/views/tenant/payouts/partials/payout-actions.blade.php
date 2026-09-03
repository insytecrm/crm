@props(['payout'])

<x-ui.action-icon-group {{ $attributes }}>
    <x-ui.action-icon
        icon="view"
        type="button"
        :title="__('View Details')"
        @click="$dispatch('open-modal', 'payout-{{ $payout->id }}')"
    />

    @if ($payout->canMarkPayoutPaid())
        <form method="POST" action="{{ route('tenant.payouts.mark-paid', $payout) }}" class="inline-flex">
            @csrf
            <x-ui.action-icon icon="complete" type="submit" :title="__('Mark Paid')" />
        </form>
    @endif
</x-ui.action-icon-group>
