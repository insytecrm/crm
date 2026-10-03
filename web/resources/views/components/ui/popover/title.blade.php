@props([
    'class' => '',
    'variant' => 'default',
])

@php
    $variantClass = match ($variant) {
        'sidebar' => 'text-white',
        default => 'text-black',
    };
@endphp

<h4 {{ $attributes->merge(['class' => 'font-medium leading-none '.$variantClass.' '.$class]) }}>
    {{ $slot }}
</h4>
