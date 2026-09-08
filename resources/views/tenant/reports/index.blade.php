@php
    $periodQuery = $periodFilter->toReportsQueryArray();
    $periodLabel = $periodFilter->period->label();
@endphp

<x-tenant-layout :title="__('Reports') . ' | InSyte CRM'">
    <div x-data="{ filtersOpen: @js($periodFilter->isReportsFiltered()) }">
        <div class="flex gap-2">
            <x-tenant.stat-card
                comfortable
                :label="__('Total Leads')"
                :value="number_format($analytics['kpis']['total_leads'])"
                accent="navy"
                :href="route('tenant.leads.index')"
            >
                <x-slot:icon>
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                    </svg>
                </x-slot:icon>
            </x-tenant.stat-card>

            <x-tenant.stat-card
                comfortable
                :label="__('Converted Leads')"
                :value="number_format($analytics['kpis']['converted_leads'])"
                accent="emerald"
                :href="route('tenant.leads.converted.index')"
            >
                <x-slot:icon>
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                </x-slot:icon>
            </x-tenant.stat-card>

            <x-tenant.stat-card
                comfortable
                :label="__('Active')"
                :value="number_format($analytics['kpis']['active_leads'])"
                accent="sky"
                :href="route('tenant.leads.index')"
            >
                <x-slot:icon>
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75Z" />
                    </svg>
                </x-slot:icon>
            </x-tenant.stat-card>

            <x-tenant.stat-card
                comfortable
                :label="__('Total Activities')"
                :value="number_format($analytics['kpis']['total_activities'])"
                accent="amber"
                :href="route('tenant.activities.index')"
            >
                <x-slot:icon>
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                    </svg>
                </x-slot:icon>
            </x-tenant.stat-card>

            <x-tenant.stat-card
                comfortable
                :label="__('Today’s Activities')"
                :value="number_format($analytics['kpis']['todays_activities'])"
                accent="cyan"
                :href="route('tenant.activities.index')"
            >
                <x-slot:icon>
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                </x-slot:icon>
            </x-tenant.stat-card>

            <x-tenant.stat-card
                comfortable
                :label="__('Lost Leads')"
                :value="number_format($analytics['kpis']['lost_leads'])"
                accent="rose"
                :href="route('tenant.leads.lost.index')"
            >
                <x-slot:icon>
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m9.75 9.75 4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                </x-slot:icon>
            </x-tenant.stat-card>
        </div>

        <x-tenant.list-toolbar class="mt-4">
            <x-ui.button type="button" variant="soft" :href="route('tenant.reports.export', $periodQuery)">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                </svg>
                {{ __('Export') }}
            </x-ui.button>

            <x-ui.button type="button" variant="soft" :href="route('tenant.reports.print', $periodQuery)" target="_blank">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a24.95 24.95 0 0 1 8.56 0m-8.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0H6.34m11.318 0h1.091c.536 0 .995-.4 1.055-.932l.826-6.888A1.125 1.125 0 0 0 18.5 9H5.5a1.125 1.125 0 0 0-1.112 1.18l.826 6.888c.06.532.519.932 1.055.932H6.34" />
                </svg>
                {{ __('Print') }}
            </x-ui.button>

            <x-ui.button
                type="button"
                variant="soft"
                @click="filtersOpen = !filtersOpen"
                x-bind:class="filtersOpen && 'ring-2 ring-navy/20'"
                x-bind:aria-expanded="filtersOpen"
            >
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 0 1-.659 1.591l-5.432 5.432a2.25 2.25 0 0 0-.659 1.591v2.927a2.25 2.25 0 0 1-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 0 0-.659-1.591L3.659 7.409A2.25 2.25 0 0 1 3 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0 1 12 3Z" />
                </svg>
                {{ __('Filter') }}
                @if ($periodFilter->isReportsFiltered())
                    <span class="inline-flex min-w-5 items-center justify-center rounded-full bg-navy px-1.5 py-0.5 text-[10px] font-bold text-white">
                        1
                    </span>
                @endif
                <svg
                    class="h-4 w-4 transition-transform duration-200"
                    :class="filtersOpen && 'rotate-180'"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke-width="2"
                    stroke="currentColor"
                    aria-hidden="true"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                </svg>
            </x-ui.button>

            <x-slot:panel>
                @include('tenant.reports.partials.filter-panel', ['periodFilter' => $periodFilter])
            </x-slot:panel>
        </x-tenant.list-toolbar>

        <div class="mt-4 space-y-4">
            <div class="grid grid-cols-1 gap-4 lg:grid-cols-2 lg:items-stretch">
                <section class="flex h-full flex-col overflow-hidden rounded-2xl border border-sky-100/80 bg-white shadow-sm shadow-sky-100/40">
                    @include('tenant.reports.partials.section-header', [
                        'title' => __('New Leads'),
                        'subtitle' => $periodLabel,
                        'headerClass' => 'border-sky-100/80 bg-gradient-to-r from-sky-50/80 to-white',
                    ])
                    <div class="flex flex-1 px-5 py-5 sm:px-6 sm:py-6">
                        @include('tenant.reports.partials.new-leads-chart', ['points' => $analytics['new_leads']])
                    </div>
                </section>

                <section class="flex h-full flex-col overflow-hidden rounded-2xl border border-emerald-100/80 bg-white shadow-sm shadow-emerald-100/40">
                    @include('tenant.reports.partials.section-header', [
                        'title' => __('Activity Trend'),
                        'subtitle' => $periodLabel,
                        'headerClass' => 'border-emerald-100/80 bg-gradient-to-r from-emerald-50/70 to-white',
                    ])
                    <div class="flex flex-1 px-5 py-5 sm:px-6 sm:py-6">
                        @include('tenant.reports.partials.activity-trend-chart', ['points' => $analytics['activity_trend']])
                    </div>
                </section>
            </div>

            <div class="grid grid-cols-1 gap-4 lg:grid-cols-3 lg:items-stretch">
                <section class="flex h-full flex-col overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm">
                    @include('tenant.reports.partials.section-header', [
                        'title' => __('Leads by Status'),
                        'subtitle' => $periodLabel,
                        'headerClass' => 'border-slate-200/80 bg-gradient-to-r from-slate-50 to-white',
                    ])
                    <div class="flex flex-1 px-5 py-5 sm:px-6 sm:py-6">
                        @include('tenant.reports.partials.pie-chart', [
                            'segments' => $analytics['by_status'],
                            'emptyMessage' => __('No status data for this period.'),
                        ])
                    </div>
                </section>

                <section class="flex h-full flex-col overflow-hidden rounded-2xl border border-violet-100/80 bg-white shadow-sm shadow-violet-100/40">
                    @include('tenant.reports.partials.section-header', [
                        'title' => __('Leads by Property Type'),
                        'subtitle' => $periodLabel,
                        'headerClass' => 'border-violet-100/80 bg-gradient-to-r from-violet-50/70 to-white',
                    ])
                    <div class="flex flex-1 px-5 py-5 sm:px-6 sm:py-6">
                        @include('tenant.reports.partials.pie-chart', [
                            'segments' => $analytics['by_property_type'],
                            'emptyMessage' => __('No property type data for this period.'),
                        ])
                    </div>
                </section>

                <section class="flex h-full flex-col overflow-hidden rounded-2xl border border-amber-100/80 bg-white shadow-sm shadow-amber-100/40">
                    @include('tenant.reports.partials.section-header', [
                        'title' => __('Leads by User'),
                        'subtitle' => $periodLabel,
                        'headerClass' => 'border-amber-100/80 bg-gradient-to-r from-amber-50/70 to-white',
                    ])
                    <div class="flex flex-1 px-5 py-5 sm:px-6 sm:py-6">
                        @include('tenant.reports.partials.pie-chart', [
                            'segments' => $analytics['by_user'],
                            'emptyMessage' => __('No assignment data for this period.'),
                        ])
                    </div>
                </section>
            </div>

            <section class="overflow-hidden rounded-2xl border border-indigo-100/80 bg-white shadow-sm shadow-indigo-100/40">
                @include('tenant.reports.partials.section-header', [
                    'title' => __('Agent Performance'),
                    'subtitle' => $periodLabel,
                    'headerClass' => 'border-indigo-100/80 bg-gradient-to-r from-indigo-50/70 to-white',
                ])
                @include('tenant.reports.partials.agent-performance-table', [
                    'rows' => $analytics['agent_performance'],
                ])
            </section>

            <div class="grid grid-cols-1 gap-4 lg:grid-cols-3 lg:items-stretch">
                @include('tenant.reports.partials.activity-status-summary-card', [
                    'title' => __('Site Visits'),
                    'subtitle' => $periodLabel,
                    'summary' => $analytics['site_visits'],
                    'headerClass' => 'border-sky-100/80 bg-gradient-to-r from-sky-50/80 to-white',
                    'borderClass' => 'border-sky-100/80',
                    'shadowClass' => 'shadow-sm shadow-sky-100/40',
                    'emptyMessage' => __('No site visits for this period.'),
                ])

                @include('tenant.reports.partials.activity-status-summary-card', [
                    'title' => __('Follow-ups'),
                    'subtitle' => $periodLabel,
                    'summary' => $analytics['follow_ups'],
                    'headerClass' => 'border-violet-100/80 bg-gradient-to-r from-violet-50/70 to-white',
                    'borderClass' => 'border-violet-100/80',
                    'shadowClass' => 'shadow-sm shadow-violet-100/40',
                    'emptyMessage' => __('No follow-ups for this period.'),
                ])

                @include('tenant.reports.partials.activity-status-summary-card', [
                    'title' => __('Tasks'),
                    'subtitle' => $periodLabel,
                    'summary' => $analytics['tasks'],
                    'headerClass' => 'border-amber-100/80 bg-gradient-to-r from-amber-50/70 to-white',
                    'borderClass' => 'border-amber-100/80',
                    'shadowClass' => 'shadow-sm shadow-amber-100/40',
                    'emptyMessage' => __('No tasks for this period.'),
                ])
            </div>
        </div>
    </div>
</x-tenant-layout>
