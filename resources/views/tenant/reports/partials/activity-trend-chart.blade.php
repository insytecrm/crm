@props(['points'])

@php
    $maxCount = max(collect($points)->max('count') ?? 0, 1);
    $count = count($points);
    $chartHeight = 40;
    $coordinates = collect($points)->values()->map(function (array $point, int $index) use ($count, $maxCount, $chartHeight): array {
        $x = $count > 1 ? ($index / ($count - 1)) * 100 : 50;
        $y = $chartHeight - (($point['count'] / $maxCount) * ($chartHeight - 4)) - 2;

        return ['x' => round($x, 2), 'y' => round($y, 2), 'count' => $point['count']];
    });

    $linePath = $coordinates
        ->map(fn (array $coordinate, int $index): string => ($index === 0 ? 'M' : 'L').$coordinate['x'].','.$coordinate['y'])
        ->implode(' ');

    $areaPath = $linePath.' L100,'.$chartHeight.' L0,'.$chartHeight.' Z';
    $hasData = collect($points)->contains(fn (array $point): bool => $point['count'] > 0);
    $labelEvery = $count > 14 ? (int) ceil($count / 8) : 1;
@endphp

<div class="w-full">
    @if ($hasData)
        <div class="relative rounded-xl bg-gradient-to-b from-emerald-50/60 to-white p-4">
            <svg viewBox="0 0 100 {{ $chartHeight }}" class="h-48 w-full overflow-visible" preserveAspectRatio="none" role="img" aria-label="{{ __('Activity trend chart') }}">
                <defs>
                    <linearGradient id="activityTrendFill" x1="0" y1="0" x2="0" y2="1">
                        <stop offset="0%" stop-color="#10b981" stop-opacity="0.35" />
                        <stop offset="100%" stop-color="#0ea5e9" stop-opacity="0.04" />
                    </linearGradient>
                    <linearGradient id="activityTrendStroke" x1="0" y1="0" x2="1" y2="0">
                        <stop offset="0%" stop-color="#059669" />
                        <stop offset="100%" stop-color="#0284c7" />
                    </linearGradient>
                </defs>
                <path d="{{ $areaPath }}" fill="url(#activityTrendFill)" />
                <path d="{{ $linePath }}" fill="none" stroke="url(#activityTrendStroke)" stroke-width="1.2" vector-effect="non-scaling-stroke" stroke-linecap="round" stroke-linejoin="round" />
                @foreach ($coordinates as $coordinate)
                    @if ($coordinate['count'] > 0)
                        <circle cx="{{ $coordinate['x'] }}" cy="{{ $coordinate['y'] }}" r="0.9" fill="#059669" vector-effect="non-scaling-stroke" />
                    @endif
                @endforeach
            </svg>
            <div class="mt-2 grid gap-1" style="grid-template-columns: repeat({{ max($count, 1) }}, minmax(0, 1fr));">
                @foreach ($points as $index => $point)
                    <div
                        @class([
                            'truncate text-center text-[10px] font-medium text-emerald-700/70',
                            'invisible' => $labelEvery > 1 && $index % $labelEvery !== 0 && $index !== $count - 1,
                        ])
                        title="{{ $point['label'] }} · {{ number_format($point['count']) }}"
                    >
                        {{ $point['label'] }}
                    </div>
                @endforeach
            </div>
        </div>
    @else
        <div class="flex h-48 items-center justify-center rounded-xl border border-dashed border-emerald-200 bg-emerald-50/40">
            <p class="text-sm text-slate-500">{{ __('No activity trend data for this period.') }}</p>
        </div>
    @endif
</div>
