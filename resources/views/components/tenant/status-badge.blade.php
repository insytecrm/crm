@props(['status'])

@php
    $classes = match ($status->value) {
        'new' => 'bg-sky-100 text-sky-700',
        'contacted' => 'bg-indigo-100 text-indigo-700',
        'qualified' => 'bg-violet-100 text-violet-700',
        'follow_up' => 'bg-amber-100 text-amber-700',
        'site_visit' => 'bg-orange-100 text-orange-700',
        'negotiation' => 'bg-purple-100 text-purple-700',
        'converted' => 'bg-emerald-100 text-emerald-700',
        'lost' => 'bg-rose-100 text-rose-700',
        default => 'bg-slate-100 text-slate-700',
    };
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold {$classes}"]) }}>
    {{ $status->label() }}
</span>
