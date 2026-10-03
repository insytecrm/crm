@props([
    'completed',
    'total',
])

@php
    $completed = max(0, min((int) $completed, (int) $total));
    $total = max(1, (int) $total);
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-0.5']) }} aria-hidden="true">
    @for ($step = 1; $step <= $total; $step++)
        <span @class([
            'size-1.5 rounded-full',
            'bg-navy' => $step <= $completed,
            'bg-slate-200' => $step > $completed,
        ])></span>
    @endfor
</span>
