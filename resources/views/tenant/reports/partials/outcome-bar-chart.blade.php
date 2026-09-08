@props([
    'points',
    'emptyMessage' => null,
    'chartId' => 'outcomeBars',
    'tone' => 'sky',
])

@php
    $maxCount = max(collect($points)->max('count') ?? 0, 1);
    $hasData = collect($points)->contains(fn (array $point): bool => $point['count'] > 0);
    $count = max(count($points), 1);
    $chartWidth = 100;
    $chartHeight = 48;
    $plotTop = 4;
    $plotBottom = 40;
    $plotHeight = $plotBottom - $plotTop;
    $slotWidth = $chartWidth / $count;
    $barWidth = min(7, max(2.2, $slotWidth * 0.58));

    $toneClasses = match ($tone) {
        'violet' => [
            'panel' => 'from-violet-50/60 to-white',
            'empty' => 'border-violet-200 bg-violet-50/40',
            'label' => 'text-violet-800/70',
        ],
        default => [
            'panel' => 'from-sky-50/60 to-white',
            'empty' => 'border-sky-200 bg-sky-50/40',
            'label' => 'text-sky-800/70',
        ],
    };
@endphp

<div class="w-full">
    @if ($hasData)
        <div @class(['rounded-xl bg-gradient-to-b p-4', $toneClasses['panel']])>
            <svg
                viewBox="0 0 {{ $chartWidth }} {{ $chartHeight }}"
                class="h-48 w-full"
                preserveAspectRatio="none"
                role="img"
                aria-label="{{ __('Outcome chart') }}"
            >
                <defs>
                    @foreach ($points as $index => $point)
                        <linearGradient id="{{ $chartId }}-{{ $index }}" x1="0" y1="1" x2="0" y2="0">
                            <stop offset="0%" stop-color="{{ $point['color'] }}" stop-opacity="0.85" />
                            <stop offset="100%" stop-color="{{ $point['color'] }}" />
                        </linearGradient>
                    @endforeach
                </defs>
                @foreach ($points as $index => $point)
                    @php
                        $barHeight = $point['count'] > 0
                            ? max(1.5, ($point['count'] / $maxCount) * $plotHeight)
                            : 0;
                        $x = ($index * $slotWidth) + (($slotWidth - $barWidth) / 2);
                        $y = $plotBottom - $barHeight;
                    @endphp
                    @if ($barHeight > 0)
                        <rect
                            x="{{ round($x, 2) }}"
                            y="{{ round($y, 2) }}"
                            width="{{ round($barWidth, 2) }}"
                            height="{{ round($barHeight, 2) }}"
                            rx="0.8"
                            fill="url(#{{ $chartId }}-{{ $index }})"
                        >
                            <title>{{ $point['label'] }} · {{ number_format($point['count']) }}</title>
                        </rect>
                    @endif
                @endforeach
            </svg>
            <div class="mt-2 grid gap-1" style="grid-template-columns: repeat({{ $count }}, minmax(0, 1fr));">
                @foreach ($points as $point)
                    <div class="min-w-0 text-center" title="{{ $point['label'] }} · {{ number_format($point['count']) }}">
                        <p @class(['truncate text-[10px] font-medium', $toneClasses['label']])>{{ $point['label'] }}</p>
                        <p class="mt-0.5 text-[10px] font-semibold tabular-nums text-slate-600">{{ number_format($point['count']) }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    @else
        <div @class(['flex h-48 items-center justify-center rounded-xl border border-dashed', $toneClasses['empty']])>
            <p class="text-sm text-slate-500">{{ $emptyMessage ?? __('No outcome data for this period.') }}</p>
        </div>
    @endif
</div>
