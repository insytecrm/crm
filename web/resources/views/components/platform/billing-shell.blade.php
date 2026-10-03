@props([
    'section' => 'overview',
])

@php
    $tabs = [
        ['key' => 'overview', 'label' => __('Overview'), 'route' => 'platform.revenue'],
        ['key' => 'subscriptions', 'label' => __('Subscriptions'), 'route' => 'platform.revenue.subscriptions'],
        ['key' => 'invoices', 'label' => __('Invoices'), 'route' => 'platform.revenue.invoices'],
        ['key' => 'payments', 'label' => __('Payments'), 'route' => 'platform.revenue.payments'],
        ['key' => 'adjustments', 'label' => __('Adjustments'), 'route' => 'platform.revenue.adjustments'],
    ];
@endphp

<nav class="mb-6 flex flex-wrap gap-1 border-b border-slate-100 pb-px">
    @foreach ($tabs as $tab)
        <a
            href="{{ route($tab['route']) }}"
            @class([
                'rounded-t-lg px-3 py-2 text-sm font-medium transition-colors',
                'border-b-2 border-navy text-navy' => $section === $tab['key'],
                'text-slate-500 hover:text-black' => $section !== $tab['key'],
            ])
        >
            {{ $tab['label'] }}
        </a>
    @endforeach
</nav>

{{ $slot }}
