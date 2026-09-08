@php
    $periodLabel = $periodFilter->period->label();
    $tenantName = tenant('name') ?? config('app.name');
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('Reports') }} · {{ $periodLabel }} | {{ $tenantName }}</title>
    <x-favicon />
    <x-fonts />
    @viteReactRefresh
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        @page {
            size: A4;
            margin: 12mm;
        }

        @media print {
            .print-actions {
                display: none !important;
            }

            body {
                background: white !important;
            }

            .print-sheet {
                box-shadow: none !important;
                border: none !important;
            }
        }
    </style>
</head>
<body class="bg-slate-100 font-sans text-black antialiased">
    <div class="print-actions fixed inset-x-0 top-0 z-50 flex items-center justify-end gap-2 border-b border-slate-200 bg-white/95 px-4 py-3 backdrop-blur print:hidden">
        <x-ui.button type="button" variant="soft" onclick="window.close()">{{ __('Close') }}</x-ui.button>
        <x-ui.button type="button" variant="primary" onclick="window.print()">{{ __('Print') }}</x-ui.button>
    </div>

    <main class="mx-auto max-w-[210mm] px-4 pb-10 pt-20 print:max-w-none print:p-0">
        <div class="print-sheet overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm print:rounded-none">
            <header class="border-b border-slate-200 bg-gradient-to-r from-slate-50 to-white px-6 py-5">
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ $tenantName }}</p>
                <h1 class="mt-1 text-2xl font-bold tracking-tight text-black">{{ __('Reports') }}</h1>
                <p class="mt-1 text-sm text-slate-500">
                    {{ __('Period') }}: {{ $periodLabel }}
                    · {{ __('Generated') }}: {{ now()->timezone(config('app.timezone'))->format('d M Y, H:i') }}
                </p>
            </header>

            <div class="space-y-5 px-6 py-5">
                <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6">
                    @foreach ([
                        ['label' => __('Total Leads'), 'value' => $analytics['kpis']['total_leads'], 'class' => 'border-slate-200'],
                        ['label' => __('Converted Leads'), 'value' => $analytics['kpis']['converted_leads'], 'class' => 'border-emerald-200 bg-emerald-50/40'],
                        ['label' => __('Active'), 'value' => $analytics['kpis']['active_leads'], 'class' => 'border-sky-200 bg-sky-50/40'],
                        ['label' => __('Total Activities'), 'value' => $analytics['kpis']['total_activities'], 'class' => 'border-amber-200 bg-amber-50/40'],
                        ['label' => __('Today’s Activities'), 'value' => $analytics['kpis']['todays_activities'], 'class' => 'border-cyan-200 bg-cyan-50/40'],
                        ['label' => __('Lost Leads'), 'value' => $analytics['kpis']['lost_leads'], 'class' => 'border-rose-200 bg-rose-50/40'],
                    ] as $kpi)
                        <div @class(['rounded-xl border px-3 py-3', $kpi['class']])>
                            <p class="text-[11px] font-medium text-slate-500">{{ $kpi['label'] }}</p>
                            <p class="mt-1 text-xl font-bold tabular-nums text-black">{{ number_format($kpi['value']) }}</p>
                        </div>
                    @endforeach
                </div>

                <div class="grid grid-cols-1 gap-4 break-inside-avoid lg:grid-cols-2">
                    <section class="overflow-hidden rounded-2xl border border-sky-100/80 bg-white">
                        @include('tenant.reports.partials.section-header', [
                            'title' => __('New Leads'),
                            'subtitle' => $periodLabel,
                            'headerClass' => 'border-sky-100/80 bg-gradient-to-r from-sky-50/80 to-white',
                        ])
                        <div class="px-5 py-5">
                            @include('tenant.reports.partials.new-leads-chart', ['points' => $analytics['new_leads']])
                        </div>
                    </section>

                    <section class="overflow-hidden rounded-2xl border border-emerald-100/80 bg-white">
                        @include('tenant.reports.partials.section-header', [
                            'title' => __('Activity Trend'),
                            'subtitle' => $periodLabel,
                            'headerClass' => 'border-emerald-100/80 bg-gradient-to-r from-emerald-50/70 to-white',
                        ])
                        <div class="px-5 py-5">
                            @include('tenant.reports.partials.activity-trend-chart', ['points' => $analytics['activity_trend']])
                        </div>
                    </section>
                </div>

                <div class="grid grid-cols-1 gap-4 break-inside-avoid lg:grid-cols-3">
                    <section class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white">
                        @include('tenant.reports.partials.section-header', [
                            'title' => __('Leads by Status'),
                            'subtitle' => $periodLabel,
                            'headerClass' => 'border-slate-200/80 bg-gradient-to-r from-slate-50 to-white',
                        ])
                        <div class="px-5 py-5">
                            @include('tenant.reports.partials.pie-chart', [
                                'segments' => $analytics['by_status'],
                                'emptyMessage' => __('No status data for this period.'),
                            ])
                        </div>
                    </section>

                    <section class="overflow-hidden rounded-2xl border border-violet-100/80 bg-white">
                        @include('tenant.reports.partials.section-header', [
                            'title' => __('Leads by Property Type'),
                            'subtitle' => $periodLabel,
                            'headerClass' => 'border-violet-100/80 bg-gradient-to-r from-violet-50/70 to-white',
                        ])
                        <div class="px-5 py-5">
                            @include('tenant.reports.partials.pie-chart', [
                                'segments' => $analytics['by_property_type'],
                                'emptyMessage' => __('No property type data for this period.'),
                            ])
                        </div>
                    </section>

                    <section class="overflow-hidden rounded-2xl border border-amber-100/80 bg-white">
                        @include('tenant.reports.partials.section-header', [
                            'title' => __('Leads by User'),
                            'subtitle' => $periodLabel,
                            'headerClass' => 'border-amber-100/80 bg-gradient-to-r from-amber-50/70 to-white',
                        ])
                        <div class="px-5 py-5">
                            @include('tenant.reports.partials.pie-chart', [
                                'segments' => $analytics['by_user'],
                                'emptyMessage' => __('No assignment data for this period.'),
                            ])
                        </div>
                    </section>
                </div>

                <section class="overflow-hidden rounded-2xl border border-indigo-100/80 bg-white break-inside-avoid">
                    @include('tenant.reports.partials.section-header', [
                        'title' => __('Agent Performance'),
                        'subtitle' => $periodLabel,
                        'headerClass' => 'border-indigo-100/80 bg-gradient-to-r from-indigo-50/70 to-white',
                    ])
                    @include('tenant.reports.partials.agent-performance-table', [
                        'rows' => $analytics['agent_performance'],
                    ])
                </section>
            </div>
        </div>
    </main>

    <script>
        window.addEventListener('load', () => {
            window.setTimeout(() => {
                const roots = document.querySelectorAll('[data-report-donut]');
                const ready = Array.from(roots).every((root) => root.childElementCount > 0);

                if (ready || roots.length === 0) {
                    window.print();
                } else {
                    window.setTimeout(() => window.print(), 600);
                }
            }, 350);
        });
    </script>
</body>
</html>
