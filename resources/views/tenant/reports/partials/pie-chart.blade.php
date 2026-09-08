@props([
    'segments',
    'emptyMessage' => null,
])

@php
    $slices = collect($segments)
        ->filter(fn (array $row): bool => $row['count'] > 0)
        ->values()
        ->map(fn (array $row): array => [
            'key' => (string) $row['key'],
            'label' => (string) $row['label'],
            'count' => (int) $row['count'],
            'percentage' => (float) $row['percentage'],
            'color' => (string) $row['color'],
        ])
        ->all();
    $hasData = $slices !== [];
@endphp

<div class="flex w-full flex-col items-center">
    @if ($hasData)
        <div
            data-report-donut
            data-segments="{{ json_encode($slices, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) }}"
            class="w-full min-h-[14rem]"
            role="img"
            aria-label="{{ __('Distribution chart') }}"
        ></div>
    @else
        <div class="flex h-40 w-full items-center justify-center rounded-xl border border-dashed border-slate-200 bg-slate-50/60">
            <p class="text-sm text-slate-500">{{ $emptyMessage ?? __('No data yet.') }}</p>
        </div>
    @endif
</div>
