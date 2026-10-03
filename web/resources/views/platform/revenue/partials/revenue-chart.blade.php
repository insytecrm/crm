@php
    $maxValue = max(
        collect($points)->max('revenue') ?? 0,
        collect($points)->max('collected') ?? 0,
        collect($points)->max('pending') ?? 0,
        1
    );
    $count = max(count($points), 1);
    $chartHeight = 120;
    $chartWidth = 560;

    $pathFor = function (string $key) use ($points, $count, $maxValue, $chartHeight, $chartWidth): string {
        if ($points === []) {
            return '';
        }

        return collect($points)->values()->map(function (array $point, int $index) use ($count, $maxValue, $chartHeight, $chartWidth, $key): string {
            $x = $count === 1 ? $chartWidth / 2 : ($index / ($count - 1)) * $chartWidth;
            $y = $chartHeight - (($point[$key] / $maxValue) * ($chartHeight - 8)) - 4;

            return ($index === 0 ? 'M' : 'L').round($x, 2).','.round($y, 2);
        })->implode(' ');
    };
@endphp

<div class="overflow-x-auto">
    <svg viewBox="0 0 {{ $chartWidth }} {{ $chartHeight }}" class="h-36 w-full min-w-[20rem]" role="img" aria-label="{{ __('Revenue chart') }}">
        <path d="{{ $pathFor('revenue') }}" fill="none" stroke="#0f766e" stroke-width="2.5" />
        <path d="{{ $pathFor('collected') }}" fill="none" stroke="#1d4ed8" stroke-width="2" stroke-dasharray="4 3" />
        <path d="{{ $pathFor('pending') }}" fill="none" stroke="#d97706" stroke-width="2" stroke-dasharray="2 3" />
    </svg>
</div>

<div class="mt-3 flex flex-wrap gap-4 text-xs font-medium text-slate-500">
    <span class="inline-flex items-center gap-1.5"><span class="size-2 rounded-full bg-teal-700"></span>{{ __('Revenue') }}</span>
    <span class="inline-flex items-center gap-1.5"><span class="size-2 rounded-full bg-blue-700"></span>{{ __('Collected') }}</span>
    <span class="inline-flex items-center gap-1.5"><span class="size-2 rounded-full bg-amber-600"></span>{{ __('Pending') }}</span>
</div>
