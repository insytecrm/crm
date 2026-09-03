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
</x-ui.action-icon-group>
