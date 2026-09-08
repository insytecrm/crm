@props([
    'label',
    'value',
    'change' => null,
    'changeDirection' => 'up',
    'href' => null,
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

    $changeClass = match ($changeDirection) {
        'down' => 'text-rose-600',
        'flat' => 'text-slate-500',
        default => 'text-emerald-600',
    };

    $classes = 'flex h-full items-center gap-3 rounded-2xl border border-slate-100 bg-white p-4 shadow-sm shadow-slate-200/40 transition-colors hover:border-slate-200 hover:bg-slate-50/40';
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
@else
    <div {{ $attributes->merge(['class' => $classes]) }}>
@endif
    @isset($icon)
        <div class="flex size-10 shrink-0 items-center justify-center rounded-xl {{ $iconBgClass }} [&_svg]:size-5">
            {{ $icon }}
        </div>
    @endisset
    <div class="min-w-0 flex-1">
        <p class="truncate text-xs font-medium text-slate-500">{{ $label }}</p>
        <p class="mt-0.5 text-2xl font-bold leading-tight tracking-tight {{ $valueTextClass }}">{{ $value }}</p>
        @if ($change)
            <p class="mt-1 truncate text-xs font-medium {{ $changeClass }}">{{ $change }}</p>
        @endif
    </div>
@if ($href)
    </a>
@else
    </div>
@endif
