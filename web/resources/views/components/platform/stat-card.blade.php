@props([
    'label',
    'value',
    'accent' => 'navy',
])

@php
    [$iconBgClass, $valueTextClass] = match ($accent) {
        'emerald' => ['bg-emerald-100 text-emerald-600', 'text-emerald-600'],
        'sky' => ['bg-sky-100 text-sky-600', 'text-sky-600'],
        'amber' => ['bg-amber-100 text-amber-600', 'text-amber-600'],
        'rose' => ['bg-rose-100 text-rose-600', 'text-rose-600'],
        default => ['bg-slate-100 text-black', 'text-black'],
    };
@endphp

<div {{ $attributes->merge(['class' => 'flex h-full items-center gap-3 rounded-xl border border-slate-100 bg-white p-3.5 shadow-sm shadow-slate-200/50']) }}>
    @isset($icon)
        <div class="flex size-9 shrink-0 items-center justify-center rounded-lg {{ $iconBgClass }} [&_svg]:size-[18px]">
            {{ $icon }}
        </div>
    @endisset
    <div class="min-w-0 flex-1">
        <p class="truncate text-xs font-medium text-slate-500">{{ $label }}</p>
        <p class="text-xl font-bold leading-tight tracking-tight {{ $valueTextClass }}">{{ $value }}</p>
    </div>
</div>
