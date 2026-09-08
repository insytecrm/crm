@props(['points'])

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
    $barWidth = min(6, max(2.5, $slotWidth * 0.55));
@endphp

<div class="w-full">
    @if ($hasData)
        <div class="rounded-xl bg-gradient-to-b from-sky-50/60 to-white p-4">
            <svg viewBox="0 0 {{ $chartWidth }} {{ $chartHeight }}" class="h-48 w-full" preserveAspectRatio="none" role="img" aria-label="{{ __('New leads chart') }}">
                <defs>
                    <linearGradient id="newLeadsBarFill" x1="0" y1="1" x2="0" y2="0">
                        <stop offset="0%" stop-color="#0284c7" />
                        <stop offset="100%" stop-color="#38bdf8" />
                    </linearGradient>
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
                            fill="url(#newLeadsBarFill)"
                        >
                            <title>{{ $point['label'] }} · {{ number_format($point['count']) }}</title>
                        </rect>
                    @endif
                @endforeach
            </svg>
            <div class="mt-2 grid gap-1" style="grid-template-columns: repeat({{ $count }}, minmax(0, 1fr));">
                @foreach ($points as $point)
                    <div class="truncate text-center text-[10px] font-medium text-sky-700/70" title="{{ $point['label'] }} · {{ number_format($point['count']) }}">
                        {{ $point['label'] }}
                    </div>
                @endforeach
            </div>
        </div>
    @else
        <div class="flex h-48 items-center justify-center rounded-xl border border-dashed border-sky-200 bg-sky-50/40">
            <p class="text-sm text-slate-500">{{ __('No new lead data for this period.') }}</p>
        </div>
    @endif
</div>
