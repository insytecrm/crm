@props([
    'title',
    'subtitle',
    'summary',
    'headerClass' => '',
    'borderClass' => 'border-slate-200/80',
    'shadowClass' => 'shadow-sm',
    'emptyMessage' => null,
])

@php
    $accentClasses = [
        'sky' => ['dot' => 'bg-sky-500', 'bar' => 'bg-sky-500', 'pill' => 'bg-sky-100 text-sky-700'],
        'amber' => ['dot' => 'bg-amber-500', 'bar' => 'bg-amber-500', 'pill' => 'bg-amber-100 text-amber-700'],
        'emerald' => ['dot' => 'bg-emerald-500', 'bar' => 'bg-emerald-500', 'pill' => 'bg-emerald-100 text-emerald-700'],
        'slate' => ['dot' => 'bg-slate-400', 'bar' => 'bg-slate-400', 'pill' => 'bg-slate-100 text-slate-700'],
    ];
@endphp

<section @class(['flex h-full flex-col overflow-hidden rounded-2xl border bg-white', $borderClass, $shadowClass])>
    @include('tenant.reports.partials.section-header', [
        'title' => $title,
        'subtitle' => $subtitle,
        'headerClass' => $headerClass,
    ])

    <div class="flex flex-1 flex-col px-5 py-5 sm:px-6 sm:py-6">
        @if ($summary['total'] === 0)
            <div class="flex flex-1 items-center justify-center py-8">
                <p class="text-sm text-slate-500">{{ $emptyMessage ?? __('No data for this period.') }}</p>
            </div>
        @else
            <div class="mb-5">
                <p class="text-3xl font-semibold tabular-nums tracking-tight text-black">{{ number_format($summary['total']) }}</p>
                <p class="mt-0.5 text-sm text-slate-500">{{ __('Total') }}</p>
            </div>

            <div class="space-y-4">
                @foreach ($summary['statuses'] as $status)
                    @php
                        $colors = $accentClasses[$status['accent']] ?? $accentClasses['slate'];
                    @endphp
                    <div>
                        <div class="mb-1.5 flex items-center justify-between gap-3">
                            <span @class(['inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-semibold', $colors['pill']])>
                                <span @class(['size-1.5 rounded-full', $colors['dot']])></span>
                                {{ $status['label'] }}
                            </span>
                            <span class="text-sm font-medium tabular-nums text-slate-700">{{ number_format($status['count']) }}</span>
                        </div>
                        <div class="h-1.5 overflow-hidden rounded-full bg-slate-100">
                            <div
                                @class(['h-full rounded-full transition-all', $colors['bar']])
                                style="width: {{ min(100, $status['percentage']) }}%"
                            ></div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-auto border-t border-slate-100 pt-4">
                <div class="flex items-center justify-between gap-3">
                    <span class="text-sm text-slate-500">{{ __('Completion') }}</span>
                    <span class="text-sm font-semibold tabular-nums text-navy">{{ number_format($summary['completion_rate'], 1) }}%</span>
                </div>
            </div>
        @endif
    </div>
</section>
