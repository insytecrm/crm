@props(['step' => 1])

@php
    $labels = [
        1 => __('Company'),
        2 => __('Plan'),
        3 => __('Pricing'),
        4 => __('Review'),
    ];
@endphp

<ol class="mb-6 flex flex-wrap items-center gap-2 text-sm">
    @for ($i = 1; $i <= 4; $i++)
        <li @class([
            'inline-flex items-center gap-2 rounded-full px-3 py-1 font-medium',
            'bg-navy text-white' => $i === $step,
            'bg-emerald-50 text-emerald-700' => $i < $step,
            'bg-slate-100 text-slate-500' => $i > $step,
        ])>
            <span class="tabular-nums">{{ $i }}</span>
            <span>{{ $labels[$i] }}</span>
        </li>
        @if ($i < 4)
            <li class="text-slate-300" aria-hidden="true">→</li>
        @endif
    @endfor
</ol>
