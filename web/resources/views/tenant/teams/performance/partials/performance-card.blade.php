@props([
    'card',
])

@php
    $rates = [
        [
            'label' => __('Conversion'),
            'value' => $card['conversion_rate'],
            'dot' => 'bg-navy',
            'bar' => 'bg-navy',
            'pill' => 'bg-slate-100 text-navy',
            'percent' => 'text-navy',
        ],
        [
            'label' => __('Follow-up completion'),
            'value' => $card['follow_up_rate'],
            'dot' => 'bg-sky-500',
            'bar' => 'bg-sky-500',
            'pill' => 'bg-sky-100 text-sky-700',
            'percent' => 'text-sky-700',
        ],
        [
            'label' => __('Site visit completion'),
            'value' => $card['site_visit_rate'],
            'dot' => 'bg-amber-500',
            'bar' => 'bg-amber-500',
            'pill' => 'bg-amber-100 text-amber-700',
            'percent' => 'text-amber-700',
        ],
        [
            'label' => __('Task completion'),
            'value' => $card['task_rate'],
            'dot' => 'bg-emerald-500',
            'bar' => 'bg-emerald-500',
            'pill' => 'bg-emerald-100 text-emerald-700',
            'percent' => 'text-emerald-700',
        ],
    ];

    $kpis = [
        ['label' => __('Total'), 'value' => $card['total_leads'], 'valueClass' => 'text-black'],
        ['label' => __('Active'), 'value' => $card['active'], 'valueClass' => 'text-sky-700'],
        ['label' => __('Converted'), 'value' => $card['converted'], 'valueClass' => 'text-emerald-700'],
        ['label' => __('Lost'), 'value' => $card['lost'], 'valueClass' => 'text-rose-700'],
    ];

    $shellClass = 'flex h-full flex-col rounded-xl border border-slate-100 bg-white p-3.5 shadow-sm transition hover:border-slate-200 hover:bg-slate-50/40';
@endphp

@if (! empty($card['href']))
    <a href="{{ $card['href'] }}" class="{{ $shellClass }}">
@else
    <div class="{{ $shellClass }}">
@endif
    <div class="flex items-start justify-between gap-2">
        <div class="min-w-0">
            <h3 class="truncate text-sm font-semibold leading-tight text-black">{{ $card['name'] }}</h3>
            @if (filled($card['subtitle']))
                <p class="mt-0.5 truncate text-[11px] text-slate-500">{{ $card['subtitle'] }}</p>
            @endif
        </div>
        <span class="inline-flex shrink-0 items-center rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-bold tabular-nums text-navy">
            #{{ $card['rank'] }}
        </span>
    </div>

    <div class="mt-3 flex flex-nowrap gap-1.5">
        @foreach ($kpis as $kpi)
            <div class="min-w-0 flex-1 rounded-lg border border-slate-100 bg-slate-50/60 px-1 py-1.5 text-center">
                <p class="truncate text-[9px] font-semibold uppercase tracking-wide text-slate-400">{{ $kpi['label'] }}</p>
                <p class="mt-0.5 text-sm font-bold tabular-nums leading-none {{ $kpi['valueClass'] }}">
                    {{ number_format($kpi['value']) }}
                </p>
            </div>
        @endforeach
    </div>

    <div class="mt-3 space-y-2.5">
        @foreach ($rates as $rate)
            <div>
                <div class="mb-1 flex items-center justify-between gap-2">
                    <span @class(['inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[10px] font-semibold', $rate['pill']])>
                        <span @class(['size-1.5 rounded-full', $rate['dot']])></span>
                        {{ $rate['label'] }}
                    </span>
                    <span @class(['text-[11px] font-semibold tabular-nums', $rate['percent']])>
                        {{ number_format($rate['value'], 1) }}%
                    </span>
                </div>
                <div class="h-2 overflow-hidden rounded-full bg-slate-100">
                    <div
                        @class(['h-full rounded-full transition-all', $rate['bar']])
                        style="width: {{ min(100, max(0, $rate['value'])) }}%"
                    ></div>
                </div>
            </div>
        @endforeach
    </div>
@if (! empty($card['href']))
    </a>
@else
    </div>
@endif
