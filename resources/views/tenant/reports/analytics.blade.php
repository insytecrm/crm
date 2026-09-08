@php
    $kpis = $metrics['kpis'];
@endphp

<x-tenant-layout :title="__('Analytics') . ' | InSyte CRM'">
    <div x-data="{ filtersOpen: @js($periodFilter->isAnalyticsFiltered()) }">
        <div class="flex gap-2">
            <x-tenant.stat-card
                comfortable
                :label="__('Leads per Day')"
                :value="$kpis['leads_per_day']['display']"
                accent="navy"
            >
                <x-slot:icon>
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
                    </svg>
                </x-slot:icon>
            </x-tenant.stat-card>

            <x-tenant.stat-card
                comfortable
                :label="__('Conversion Rate')"
                :value="$kpis['conversion_rate']['display']"
                accent="emerald"
            >
                <x-slot:icon>
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18 9 11.25l4.306 4.306a11.95 11.95 0 0 1 5.814-5.518l2.74-1.22m0 0-5.94-2.281m5.94 2.28-2.28 5.941" />
                    </svg>
                </x-slot:icon>
            </x-tenant.stat-card>

            <x-tenant.stat-card
                comfortable
                :label="__('Avg Response Time')"
                :value="$kpis['avg_response_time']['display']"
                accent="sky"
            >
                <x-slot:icon>
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                </x-slot:icon>
            </x-tenant.stat-card>

            <x-tenant.stat-card
                comfortable
                :label="__('Engagement')"
                :value="$kpis['engagement']['display']"
                accent="amber"
            >
                <x-slot:icon>
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.362 5.214A8.252 8.252 0 0 1 12 21 8.25 8.25 0 0 1 6.038 7.047 8.287 8.287 0 0 0 9 9.601a8.983 8.983 0 0 1 3.361-6.867 8.21 8.21 0 0 0 3 2.48Z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 18a3.75 3.75 0 0 0 .495-7.468 5.99 5.99 0 0 0-1.925 3.547 5.975 5.975 0 0 1-2.133-1.001A3.75 3.75 0 0 0 12 18Z" />
                    </svg>
                </x-slot:icon>
            </x-tenant.stat-card>

            <x-tenant.stat-card
                comfortable
                :label="__('Follow-up Rate')"
                :value="$kpis['follow_up_rate']['display']"
                accent="cyan"
            >
                <x-slot:icon>
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182" />
                    </svg>
                </x-slot:icon>
            </x-tenant.stat-card>

            <x-tenant.stat-card
                comfortable
                :label="__('Site Visits')"
                :value="$kpis['site_visit_rate']['display']"
                accent="rose"
            >
                <x-slot:icon>
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                    </svg>
                </x-slot:icon>
            </x-tenant.stat-card>
        </div>

        <x-tenant.list-toolbar class="mt-4">
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
                @if ($periodFilter->isAnalyticsFiltered())
                    <span class="inline-flex min-w-5 items-center justify-center rounded-full bg-navy px-1.5 py-0.5 text-[10px] font-bold text-white">
                        {{ $periodFilter->analyticsActiveCount() }}
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
                @include('tenant.reports.partials.filter-panel', [
                    'periodFilter' => $periodFilter,
                    'action' => route('tenant.reports.analytics'),
                    'clearHref' => route('tenant.reports.analytics'),
                    'showScopeFilters' => true,
                    'teams' => $teams,
                    'users' => $users,
                ])
            </x-slot:panel>
        </x-tenant.list-toolbar>

        <div class="mt-4 grid grid-cols-1 gap-4 lg:grid-cols-12 lg:items-stretch">
            <section class="flex min-w-0 flex-col overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm lg:col-span-8">
                @include('tenant.reports.partials.section-header', [
                    'title' => __('Lead Velocity Trend'),
                    'subtitle' => $periodFilter->period->label(),
                    'headerClass' => 'border-slate-200/80 bg-gradient-to-r from-slate-50 to-white',
                ])
                <div class="flex flex-1 px-5 py-5 sm:px-6 sm:py-6">
                    @include('tenant.reports.partials.lead-velocity-chart', [
                        'points' => $metrics['lead_velocity'],
                    ])
                </div>
            </section>

            <aside class="flex min-w-0 flex-col overflow-hidden rounded-2xl border border-violet-100/80 bg-white shadow-sm shadow-violet-100/40 lg:col-span-4">
                @include('tenant.reports.partials.section-header', [
                    'title' => __('Lead Aging'),
                    'subtitle' => $periodFilter->period->label(),
                    'headerClass' => 'border-violet-100/80 bg-gradient-to-r from-violet-50/70 to-white',
                ])
                <div class="flex flex-1 items-center px-5 py-5 sm:px-6 sm:py-6">
                    @include('tenant.reports.partials.pie-chart', [
                        'segments' => $metrics['lead_aging'],
                        'emptyMessage' => __('No open leads to age for this period.'),
                    ])
                </div>
            </aside>
        </div>

        <div class="mt-4 grid grid-cols-1 gap-4 lg:grid-cols-12" style="align-items: start;">
            <div class="flex min-w-0 flex-col gap-4 lg:col-span-8">
                <section class="overflow-hidden rounded-2xl border border-sky-100/80 bg-white shadow-sm shadow-sky-100/40">
                    @include('tenant.reports.partials.section-header', [
                        'title' => __('Property Type Performance'),
                        'subtitle' => $periodFilter->period->label(),
                        'headerClass' => 'border-sky-100/80 bg-gradient-to-r from-sky-50/80 to-white',
                    ])
                    @include('tenant.reports.partials.property-type-performance-table', [
                        'rows' => $metrics['property_type_performance'],
                    ])
                </section>

                @include('tenant.reports.partials.source-performance-panel', [
                    'rows' => $metrics['source_performance'],
                    'periodLabel' => $periodFilter->period->label(),
                ])
            </div>

            <aside class="min-w-0 overflow-hidden rounded-2xl border border-amber-100/80 bg-white shadow-sm shadow-amber-100/40 lg:col-span-4">
                @include('tenant.reports.partials.section-header', [
                    'title' => __('Top Leads'),
                    'subtitle' => $periodFilter->period->label(),
                    'headerClass' => 'border-amber-100/80 bg-gradient-to-r from-amber-50/70 to-white',
                ])
                @include('tenant.reports.partials.top-leads-table', [
                    'leads' => $metrics['top_leads'],
                ])
            </aside>
        </div>

        <div class="mt-4 grid grid-cols-1 gap-4 lg:grid-cols-2 lg:items-stretch">
            <section class="flex min-w-0 flex-col overflow-hidden rounded-2xl border border-sky-100/80 bg-white shadow-sm shadow-sky-100/40">
                @include('tenant.reports.partials.section-header', [
                    'title' => __('Site Visit Outcomes'),
                    'subtitle' => $periodFilter->period->label(),
                    'headerClass' => 'border-sky-100/80 bg-gradient-to-r from-sky-50/80 to-white',
                ])
                <div class="flex flex-1 flex-col px-5 py-5 sm:px-6 sm:py-6">
                    @if ($metrics['site_visit_outcomes']['total'] > 0)
                        <div class="mb-4">
                            <p class="text-3xl font-semibold tabular-nums tracking-tight text-black">{{ number_format($metrics['site_visit_outcomes']['total']) }}</p>
                            <p class="mt-0.5 text-sm text-slate-500">{{ __('Completed with outcomes') }}</p>
                        </div>
                    @endif
                    @include('tenant.reports.partials.outcome-bar-chart', [
                        'points' => $metrics['site_visit_outcomes']['points'],
                        'chartId' => 'siteVisitOutcomes',
                        'tone' => 'sky',
                        'emptyMessage' => __('No site visit outcomes for this period.'),
                    ])
                </div>
            </section>

            <section class="flex min-w-0 flex-col overflow-hidden rounded-2xl border border-violet-100/80 bg-white shadow-sm shadow-violet-100/40">
                @include('tenant.reports.partials.section-header', [
                    'title' => __('Follow-up Outcomes'),
                    'subtitle' => $periodFilter->period->label(),
                    'headerClass' => 'border-violet-100/80 bg-gradient-to-r from-violet-50/70 to-white',
                ])
                <div class="flex flex-1 flex-col px-5 py-5 sm:px-6 sm:py-6">
                    @if ($metrics['follow_up_outcomes']['total'] > 0)
                        <div class="mb-4">
                            <p class="text-3xl font-semibold tabular-nums tracking-tight text-black">{{ number_format($metrics['follow_up_outcomes']['total']) }}</p>
                            <p class="mt-0.5 text-sm text-slate-500">{{ __('Completed with outcomes') }}</p>
                        </div>
                    @endif
                    @include('tenant.reports.partials.outcome-bar-chart', [
                        'points' => $metrics['follow_up_outcomes']['points'],
                        'chartId' => 'followUpOutcomes',
                        'tone' => 'violet',
                        'emptyMessage' => __('No follow-up outcomes for this period.'),
                    ])
                </div>
            </section>
        </div>
    </div>
</x-tenant-layout>
