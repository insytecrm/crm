@props([
    'rows',
    'periodLabel',
    'emptyMessage' => null,
])

@php
    $metrics = [
        ['key' => 'total', 'label' => __('Total Leads'), 'color' => '#5470C6'],
        ['key' => 'active', 'label' => __('Active'), 'color' => '#91CC75'],
        ['key' => 'converted', 'label' => __('Converted'), 'color' => '#FC8452'],
        ['key' => 'lost', 'label' => __('Lost'), 'color' => '#FAC858'],
    ];

    $maxValue = max(
        (int) collect($rows)->max(fn (array $row): int => max(
            (int) $row['total'],
            (int) $row['active'],
            (int) $row['converted'],
            (int) $row['lost'],
        )) ?: 0,
        1,
    );

    $tickCount = min(4, max($maxValue, 1));
@endphp

<section
    x-data="{ view: 'table' }"
    class="overflow-hidden rounded-2xl border border-indigo-100/80 bg-white shadow-sm shadow-indigo-100/40"
>
    <div class="border-b border-indigo-100/80 bg-gradient-to-r from-indigo-50/70 to-white px-5 py-4 sm:px-6 sm:py-5">
        <div class="flex items-start justify-between gap-3">
            <div class="min-w-0">
                <h2 class="text-base font-semibold leading-snug text-black">{{ __('Source Wise Performance') }}</h2>
                <p class="mt-0.5 text-sm leading-snug text-slate-500">{{ $periodLabel }}</p>
            </div>

            <div
                class="inline-flex shrink-0 items-center rounded-lg border border-slate-200 bg-white p-0.5 shadow-sm"
                role="group"
                aria-label="{{ __('Source performance view') }}"
            >
                <button
                    type="button"
                    @click="view = 'table'"
                    class="inline-flex size-8 items-center justify-center rounded-md text-slate-500 transition"
                    x-bind:class="view === 'table' ? 'bg-slate-100 text-navy' : 'hover:bg-slate-50 hover:text-slate-700'"
                    x-bind:aria-pressed="view === 'table'"
                    title="{{ __('Table view') }}"
                >
                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.375 19.5h17.25M3.375 12h17.25M3.375 4.5h17.25" />
                    </svg>
                    <span class="sr-only">{{ __('Table') }}</span>
                </button>
                <button
                    type="button"
                    @click="view = 'chart'"
                    class="inline-flex size-8 items-center justify-center rounded-md text-slate-500 transition"
                    x-bind:class="view === 'chart' ? 'bg-slate-100 text-navy' : 'hover:bg-slate-50 hover:text-slate-700'"
                    x-bind:aria-pressed="view === 'chart'"
                    title="{{ __('Chart view') }}"
                >
                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 3v18h18M7.5 14.25v3.75M12 9.75v8.25M16.5 6v12" />
                    </svg>
                    <span class="sr-only">{{ __('Chart') }}</span>
                </button>
            </div>
        </div>
    </div>

    {{-- Fixed body height so table/chart toggle never resizes the card --}}
    <div class="relative overflow-hidden" style="height: 22rem;">
        <div x-show="view === 'table'" class="absolute inset-0 overflow-auto">
            @include('tenant.reports.partials.source-performance-table', [
                'rows' => $rows,
                'emptyMessage' => $emptyMessage,
            ])
        </div>

        <div
            x-show="view === 'chart'"
            class="absolute inset-0 flex flex-col px-5 py-4 sm:px-6"
            style="display: none;"
        >
            @if (count($rows) > 0)
                <div class="min-h-0 flex-1 overflow-auto">
                    <div class="space-y-2.5 pr-1">
                        @foreach ($rows as $row)
                            <div class="grid items-center gap-3" style="grid-template-columns: 6.75rem minmax(0, 1fr);">
                                <p class="truncate text-xs font-medium text-slate-600" title="{{ $row['source'] }}">
                                    {{ $row['source'] }}
                                </p>
                                <div class="relative space-y-1 py-0.5">
                                    <div class="pointer-events-none absolute inset-0 flex">
                                        @for ($tick = 0; $tick < $tickCount; $tick++)
                                            <div @class([
                                                'h-full flex-1',
                                                'border-r border-dashed border-slate-200' => $tick < $tickCount - 1,
                                            ])></div>
                                        @endfor
                                    </div>
                                    @foreach ($metrics as $metric)
                                        @php
                                            $value = (int) $row[$metric['key']];
                                            $width = $maxValue > 0
                                                ? max(($value / $maxValue) * 100, $value > 0 ? 1.5 : 0)
                                                : 0;
                                        @endphp
                                        <div class="relative h-2 w-full">
                                            @if ($width > 0)
                                                <div
                                                    class="h-2 rounded-full"
                                                    style="width: {{ round($width, 2) }}%; background-color: {{ $metric['color'] }};"
                                                    title="{{ $metric['label'] }}: {{ number_format($value) }}"
                                                ></div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-2 flex items-center justify-between border-t border-transparent pt-1 text-[10px] tabular-nums text-slate-400" style="margin-left: 6.75rem; padding-left: 0.75rem;">
                        <span>0</span>
                        @for ($tick = 1; $tick <= $tickCount; $tick++)
                            <span>{{ (int) round(($maxValue / $tickCount) * $tick) }}</span>
                        @endfor
                    </div>
                </div>

                <div class="mt-2 flex flex-shrink-0 flex-wrap items-center justify-center gap-x-4 gap-y-1.5 border-t border-slate-100 pt-3">
                    @foreach ($metrics as $metric)
                        <div class="inline-flex items-center gap-1.5 text-xs text-slate-600">
                            <span class="size-2.5 rounded-full" style="background-color: {{ $metric['color'] }}" aria-hidden="true"></span>
                            {{ $metric['label'] }}
                        </div>
                    @endforeach
                </div>
            @else
                <div class="flex h-full items-center justify-center">
                    <p class="text-sm text-slate-500">{{ $emptyMessage ?? __('No source performance data for this period.') }}</p>
                </div>
            @endif
        </div>
    </div>
</section>
