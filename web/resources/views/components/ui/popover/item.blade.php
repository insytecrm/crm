@props([
    'as' => 'a',
    'variant' => 'default',
])

@php
    $variantClass = match ($variant) {
        'sidebar' => 'text-white hover:bg-white/10',
        default => 'text-black hover:bg-slate-100',
    };
@endphp

<{{ $as }} {{ $attributes->merge(['class' => 'flex w-full items-center rounded-md px-2 py-1.5 text-sm font-medium '.$variantClass]) }}>
    {{ $slot }}
</{{ $as }}>
