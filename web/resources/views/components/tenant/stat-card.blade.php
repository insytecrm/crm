@props([
    'label',
    'value',
    'accent' => 'navy',
    'href' => null,
    'active' => false,
    'compact' => false,
    'comfortable' => false,
])

@php
    [$iconBgClass, $valueTextClass] = match ($accent) {
        'emerald' => ['bg-emerald-100 text-emerald-600', 'text-emerald-600'],
        'sky' => ['bg-sky-100 text-sky-600', 'text-sky-600'],
        'amber' => ['bg-amber-100 text-amber-600', 'text-amber-600'],
        'rose' => ['bg-rose-100 text-rose-600', 'text-rose-600'],
        'cyan' => ['bg-cyan-100 text-cyan-600', 'text-cyan-600'],
        default => ['bg-slate-100 text-black', 'text-black'],
    };

    $paddingClass = match (true) {
        $comfortable => 'min-w-0 flex-1 gap-3 px-3 py-3',
        $compact => 'min-w-0 flex-1 gap-2 p-2',
        default => 'gap-3 p-3.5',
    };

    $classes = collect([
        'flex h-full items-center rounded-xl border bg-white shadow-sm shadow-slate-200/50 transition-colors',
        $paddingClass,
        $active ? 'border-navy ring-1 ring-navy/10' : 'border-slate-100 hover:border-slate-200 hover:bg-slate-50/50',
    ])->implode(' ');

    $iconWrapperClass = match (true) {
        $comfortable => 'size-9 [&_svg]:size-[18px]',
        $compact => 'size-7 [&_svg]:size-3.5',
        default => 'size-9 [&_svg]:size-[18px]',
    };

    $labelClass = match (true) {
        $comfortable => 'text-xs',
        $compact => 'text-[10px] leading-tight',
        default => 'text-xs',
    };

    $valueSizeClass = match (true) {
        $comfortable => 'text-xl',
        $compact => 'text-lg',
        default => 'text-xl',
    };
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
@else
    <div {{ $attributes->merge(['class' => $classes]) }}>
@endif
    <div class="flex shrink-0 items-center justify-center rounded-lg {{ $iconWrapperClass }} {{ $iconBgClass }}">
        {{ $icon }}
    </div>
    <div class="min-w-0 flex-1">
        <p class="truncate font-medium text-slate-500 {{ $labelClass }}">{{ $label }}</p>
        <p class="font-bold leading-tight tracking-tight {{ $valueSizeClass }} {{ $valueTextClass }}">{{ $value }}</p>
    </div>
@if ($href)
    </a>
@else
    </div>
@endif
