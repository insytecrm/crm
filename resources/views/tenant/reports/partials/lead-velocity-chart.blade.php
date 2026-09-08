@props(['points'])

@php
    $series = collect($points)->values();
    $hasData = $series->contains(fn (array $point): bool => $point['cumulative'] > 0);
    $count = $series->count();
    $maxValue = max((int) ($series->max('cumulative') ?? 0), 1);
    $range = max($maxValue, 1);
    $chartWidth = 100;
    $chartHeight = 52;
    $plotTop = 6;
    $plotBottom = 44;
    $plotHeight = $plotBottom - $plotTop;
    $startValue = (int) ($series->first()['cumulative'] ?? 0);
    $endValue = (int) ($series->last()['cumulative'] ?? 0);
    $delta = $endValue - $startValue;
    $isUp = $delta >= 0;

    $coordinates = $series->map(function (array $point, int $index) use ($count, $maxValue, $range, $plotTop, $plotHeight): array {
        $x = $count > 1 ? ($index / ($count - 1)) * 100 : 0;
        $y = $plotTop + ((1 - (($point['cumulative'] - 0) / $range)) * $plotHeight);

        return [
            'x' => round($x, 2),
            'y' => round($y, 2),
            'cumulative' => $point['cumulative'],
            'count' => $point['count'],
            'label' => $point['label'],
        ];
    });

    $linePath = $coordinates
        ->map(fn (array $coordinate, int $index): string => ($index === 0 ? 'M' : 'L').$coordinate['x'].','.$coordinate['y'])
        ->implode(' ');

    $areaPath = $linePath.' L100,'.$plotBottom.' L0,'.$plotBottom.' Z';
    $labelEvery = $count > 16 ? (int) ceil($count / 8) : ($count > 10 ? 2 : 1);
    $gridLines = [0.25, 0.5, 0.75, 1.0];
    $stroke = $isUp ? '#059669' : '#e11d48';
    $fillTop = $isUp ? '#10b981' : '#f43f5e';
@endphp

<div class="w-full">
    @if ($hasData)
        <div class="rounded-xl bg-gradient-to-b from-slate-50/80 to-white p-4">
            <div class="mb-3 flex items-end justify-between gap-3">
                <div>
                    <p class="text-[11px] font-medium uppercase tracking-wide text-slate-400">{{ __('Cumulative leads') }}</p>
                    <p class="mt-0.5 text-2xl font-bold tabular-nums tracking-tight text-black">{{ number_format($endValue) }}</p>
                </div>
                <div @class([
                    'inline-flex items-center gap-1 rounded-lg px-2 py-1 text-xs font-semibold tabular-nums',
                    'bg-emerald-50 text-emerald-700' => $isUp,
                    'bg-rose-50 text-rose-700' => ! $isUp,
                ])>
                    <span aria-hidden="true">{{ $isUp ? '▲' : '▼' }}</span>
                    {{ $delta >= 0 ? '+' : '' }}{{ number_format($delta) }}
                </div>
            </div>

            <svg
                viewBox="0 0 {{ $chartWidth }} {{ $chartHeight }}"
                class="h-56 w-full overflow-visible"
                preserveAspectRatio="none"
                role="img"
                aria-label="{{ __('Lead velocity trend chart') }}"
            >
                <defs>
                    <linearGradient id="leadVelocityFill" x1="0" y1="0" x2="0" y2="1">
                        <stop offset="0%" stop-color="{{ $fillTop }}" stop-opacity="0.28" />
                        <stop offset="100%" stop-color="{{ $fillTop }}" stop-opacity="0.02" />
                    </linearGradient>
                </defs>

                @foreach ($gridLines as $ratio)
                    @php $y = $plotTop + ((1 - $ratio) * $plotHeight); @endphp
                    <line
                        x1="0"
                        y1="{{ round($y, 2) }}"
                        x2="100"
                        y2="{{ round($y, 2) }}"
                        stroke="#e2e8f0"
                        stroke-width="0.35"
                        stroke-dasharray="1.2 1.2"
                        vector-effect="non-scaling-stroke"
                    />
                @endforeach

                <path d="{{ $areaPath }}" fill="url(#leadVelocityFill)" />
                <path
                    d="{{ $linePath }}"
                    fill="none"
                    stroke="{{ $stroke }}"
                    stroke-width="1.6"
                    vector-effect="non-scaling-stroke"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                />

                @if ($coordinates->isNotEmpty())
                    @php $last = $coordinates->last(); @endphp
                    <circle
                        cx="{{ $last['x'] }}"
                        cy="{{ $last['y'] }}"
                        r="1.35"
                        fill="#ffffff"
                        stroke="{{ $stroke }}"
                        stroke-width="0.7"
                        vector-effect="non-scaling-stroke"
                    />
                @endif
            </svg>

            <div class="mt-2 grid gap-1" style="grid-template-columns: repeat({{ max($count, 1) }}, minmax(0, 1fr));">
                @foreach ($series as $index => $point)
                    <div
                        @class([
                            'truncate text-center text-[10px] font-medium text-slate-500',
                            'invisible' => $labelEvery > 1 && $index % $labelEvery !== 0 && $index !== $count - 1,
                        ])
                        title="{{ $point['label'] }} · +{{ number_format($point['count']) }} · {{ number_format($point['cumulative']) }} {{ __('total') }}"
                    >
                        {{ $point['label'] }}
                    </div>
                @endforeach
            </div>
        </div>
    @else
        <div class="flex h-56 items-center justify-center rounded-xl border border-dashed border-slate-200 bg-slate-50/60">
            <p class="text-sm text-slate-500">{{ __('No lead velocity data for this period.') }}</p>
        </div>
    @endif
</div>
