@props([
    'priority',
])

@php
    $classes = match ($priority->value) {
        'high' => 'bg-rose-100 text-rose-700',
        'low' => 'bg-slate-100 text-slate-500',
        default => 'bg-slate-100 text-slate-600',
    };
@endphp

<span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $classes }}">
    {{ $priority->label() }}
</span>
