@props([
    'alt' => 'InSyte CRM',
    'variant' => 'light',
])

@php
    $logoSrc = match ($variant) {
        'dark' => global_asset('images/'.rawurlencode('inSyte (2).png')),
        default => global_asset('images/2.png'),
    };
@endphp

<div {{ $attributes->merge(['class' => 'flex items-center']) }}>
    <img
        src="{{ $logoSrc }}"
        alt="{{ $alt }}"
        class="sidebar-label h-8 w-auto max-w-[196px] object-contain object-left"
    >

    <img
        src="{{ global_asset('images/5.png') }}"
        alt=""
        class="sidebar-brand-mark h-9 w-9 object-contain"
        aria-hidden="true"
    >
</div>
