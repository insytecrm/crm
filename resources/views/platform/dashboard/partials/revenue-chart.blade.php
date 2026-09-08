@props(['points'])

@php
    $maxRevenue = max(collect($points)->max('revenue') ?? 0, 1);
    $count = count($points);
    $chartHeight = 40;
    $coordinates = collect($points)->values()->map(function (array $point, int $index) use ($count, $maxRevenue, $chartHeight): array {
        $x = $count > 1 ? ($index / ($count - 1)) * 100 : 50;
        $y = $chartHeight - (($point['revenue'] / $maxRevenue) * ($chartHeight - 2)) - 1;

        return ['x' => round($x, 2), 'y' => round($y, 2)];
    });

    $linePath = $coordinates
        ->map(fn (array $coordinate, int $index): string => ($index === 0 ? 'M' : 'L').$coordinate['x'].','.$coordinate['y'])
        ->implode(' ');

    $areaPath = $linePath.' L100,'.$chartHeight.' L0,'.$chartHeight.' Z';
    $hasData = collect($points)->contains(fn (array $point): bool => $point['revenue'] > 0);
@endphp

<div>
    @if ($hasData)
        <div class="relative rounded-xl bg-slate-50/70 p-3 sm:p-4">
            <svg viewBox="0 0 100 {{ $chartHeight }}" class="h-40 w-full sm:h-44" preserveAspectRatio="none" aria-hidden="true">
                <defs>
                    <linearGradient id="platformRevenueFill" x1="0" y1="0" x2="0" y2="1">
                        <stop offset="0%" stop-color="#34b6ff" stop-opacity="0.28" />
                        <stop offset="100%" stop-color="#34b6ff" stop-opacity="0.02" />
                    </linearGradient>
                </defs>
                <path d="{{ $areaPath }}" fill="url(#platformRevenueFill)" />
                <path d="{{ $linePath }}" fill="none" stroke="#34b6ff" stroke-width="0.9" vector-effect="non-scaling-stroke" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
            <div class="mt-2 grid gap-1" style="grid-template-columns: repeat({{ max($count, 1) }}, minmax(0, 1fr));">
                @foreach ($points as $point)
                    <div class="truncate text-center text-[10px] font-medium text-slate-500" title="{{ $point['label'] }} · ₹{{ number_format($point['revenue']) }}">
                        {{ $point['label'] }}
                    </div>
                @endforeach
            </div>
        </div>
    @else
        <div class="flex h-40 items-center justify-center rounded-xl border border-dashed border-slate-200 bg-slate-50/60 sm:h-44">
            <p class="text-sm text-slate-500">{{ __('No revenue data yet.') }}</p>
        </div>
    @endif
</div>
