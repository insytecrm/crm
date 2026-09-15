@props([
    'invoice',
    'context' => 'list',
])

@php
    use Illuminate\Support\Js;

    $isList = $context === 'list';
    $recipientEmail = $invoice->billed_to_email ?: $invoice->tenant?->email ?: '';
    $sendListClick = "\$dispatch('open-send-invoice-list', ".Js::from([
        'sendUrl' => route('platform.revenue.invoices.send', $invoice),
        'partner' => $invoice->tenant?->name ?? $invoice->billed_to_name ?? '—',
        'email' => $recipientEmail,
        'number' => $invoice->number,
        'total' => \App\Support\Platform\BillingMoney::format((int) $invoice->total),
    ]).")";
@endphp

@if ($isList)
    <x-ui.action-icon-group {{ $attributes }}>
        <x-ui.action-icon
            icon="view"
            :href="route('platform.revenue.invoices.show', $invoice)"
            :title="__('View')"
        />
        <x-ui.action-icon
            icon="download"
            :href="route('platform.revenue.invoices.download', $invoice)"
            :title="__('Download')"
        />
        <x-ui.action-icon
            icon="play"
            type="button"
            :title="__('Send')"
            x-on:click="{!! $sendListClick !!}"
        />
        @if ($invoice->status !== \App\Enums\BillingInvoiceStatus::Paid)
            <form method="POST" action="{{ route('platform.revenue.invoices.mark-paid', $invoice) }}" class="inline" @submit.prevent="if (confirm(@js(__('Mark this invoice as paid?')))) { $el.submit(); }">
                @csrf
                <x-ui.action-icon icon="complete" type="submit" :title="__('Mark Paid')" />
            </form>
        @endif
    </x-ui.action-icon-group>
@else
    <div {{ $attributes->class(['inline-flex flex-wrap items-center justify-end gap-1.5']) }}>
        <x-ui.button variant="soft" :href="route('platform.revenue.invoices.download', $invoice)" :title="__('Download')">
            {{ __('Download') }}
        </x-ui.button>
        <x-ui.button type="button" variant="default" :title="__('Send')" @click="$dispatch('open-send-invoice')">
            {{ __('Send') }}
        </x-ui.button>
        @if ($invoice->status !== \App\Enums\BillingInvoiceStatus::Paid)
            <form method="POST" action="{{ route('platform.revenue.invoices.mark-paid', $invoice) }}" @submit.prevent="if (confirm(@js(__('Mark this invoice as paid?')))) { $el.submit(); }">
                @csrf
                <x-ui.button type="submit" variant="success">{{ __('Mark Paid') }}</x-ui.button>
            </form>
        @endif
    </div>
@endif
