@props([
    'class' => '',
    'variant' => 'default',
])

@php
    $variantClass = match ($variant) {
        'sidebar' => 'text-white/70',
        default => 'text-slate-500',
    };
@endphp

<p {{ $attributes->merge(['class' => 'text-sm '.$variantClass.' '.$class]) }}>
    {{ $slot }}
</p>
