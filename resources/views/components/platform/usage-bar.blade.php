@props([
    'percent' => null,
])

@php
    $width = $percent === null ? 0 : max(0, min(100, (float) $percent));
@endphp

<div {{ $attributes->merge(['class' => 'h-2 w-full overflow-hidden rounded-full bg-slate-100']) }}>
    <div
        class="h-full rounded-full bg-navy transition-all"
        style="width: {{ $width }}%"
    ></div>
</div>
