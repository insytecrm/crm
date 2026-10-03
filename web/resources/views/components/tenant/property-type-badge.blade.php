@props(['propertyType'])

@php
    $classes = match ($propertyType->value) {
        'apartment' => 'bg-sky-100 text-sky-700',
        'villa' => 'bg-emerald-100 text-emerald-700',
        'plot' => 'bg-amber-100 text-amber-700',
        'shop' => 'bg-violet-100 text-violet-700',
        'office' => 'bg-indigo-100 text-indigo-700',
        default => 'bg-slate-100 text-slate-700',
    };
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold {$classes}"]) }}>
    {{ $propertyType->label() }}
</span>
