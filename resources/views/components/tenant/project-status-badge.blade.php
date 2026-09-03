@props(['status'])

@php
    $classes = match ($status->value) {
        'new_launch' => 'bg-sky-100 text-sky-700',
        'under_construction' => 'bg-amber-100 text-amber-700',
        'ready_to_move' => 'bg-emerald-100 text-emerald-700',
        default => 'bg-slate-100 text-slate-700',
    };
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold {$classes}"]) }}>
    {{ $status->label() }}
</span>
