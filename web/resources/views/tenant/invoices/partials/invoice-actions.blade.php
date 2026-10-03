@props(['invoice'])

<x-ui.action-icon-group {{ $attributes }}>
    <x-ui.action-icon
        icon="view"
        type="button"
        :title="__('View Invoice')"
        @click="$dispatch('open-modal', 'invoice-{{ $invoice->id }}')"
    />

    <x-ui.action-icon
        icon="download"
        :href="route('tenant.invoices.pdf', $invoice)"
        :title="__('Download PDF')"
    />

    @if ($invoice->canMarkPayoutPaid())
        <form method="POST" action="{{ route('tenant.invoices.mark-paid', $invoice) }}" class="inline-flex">
            @csrf
            <x-ui.action-icon icon="complete" type="submit" :title="__('Mark Paid')" />
        </form>
    @endif
</x-ui.action-icon-group>
