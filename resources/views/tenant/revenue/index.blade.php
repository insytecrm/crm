<x-tenant-layout :title="__('Revenue') . ' | InSyte CRM'">
    <div x-data="{ filtersOpen: @js($filter->isActive()) }">
        {{-- 1. Revenue KPIs --}}
        <div class="mb-4 grid grid-cols-1 gap-3 sm:grid-cols-2 sm:items-stretch lg:grid-cols-3">
            <x-tenant.stat-card :label="__('Total Revenue')" :value="'₹' . number_format($summary['total_revenue'])" accent="emerald">
                <x-slot:icon>
                    <svg class="size-[18px]" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                </x-slot:icon>
            </x-tenant.stat-card>
            <x-tenant.stat-card :label="__('Revenue This Month')" :value="'₹' . number_format($summary['revenue_this_month'])" accent="sky">
                <x-slot:icon>
                    <svg class="size-[18px]" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                    </svg>
                </x-slot:icon>
            </x-tenant.stat-card>
            <x-tenant.stat-card :label="__('Revenue This Quarter')" :value="'₹' . number_format($summary['revenue_this_quarter'])" accent="sky">
                <x-slot:icon>
                    <svg class="size-[18px]" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3v11.25A2.25 2.25 0 0 0 6 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0 1 18 16.5h-2.25m-7.5 0h7.5m-7.5 0-1 3m8.5-3 1 3m0 0 .5 1.5m-.5-1.5h-9.5m0 0-.5 1.5" />
                    </svg>
                </x-slot:icon>
            </x-tenant.stat-card>
            <x-tenant.stat-card :label="__('Total Commission')" :value="'₹' . number_format($summary['total_commission'])" accent="navy">
                <x-slot:icon>
                    <svg class="size-[18px]" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z" />
                    </svg>
                </x-slot:icon>
            </x-tenant.stat-card>
            <x-tenant.stat-card :label="__('Pending Commission')" :value="'₹' . number_format($summary['pending_commission'])" accent="amber">
                <x-slot:icon>
                    <svg class="size-[18px]" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                </x-slot:icon>
            </x-tenant.stat-card>
            <x-tenant.stat-card :label="__('Received Commission')" :value="'₹' . number_format($summary['received_commission'])" accent="emerald">
                <x-slot:icon>
                    <svg class="size-[18px]" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                </x-slot:icon>
            </x-tenant.stat-card>
        </div>

        <x-tenant.list-toolbar>
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
                {{ __('Filters') }}
                @if ($filter->isActive())
                    <span class="inline-flex min-w-5 items-center justify-center rounded-full bg-navy px-1.5 py-0.5 text-[10px] font-bold text-white">
                        {{ $filter->activeCount() }}
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
            @if ($filter->isActive())
                <x-ui.button type="button" variant="soft" :href="route('tenant.revenue.index')">
                    {{ __('Clear filters') }}
                </x-ui.button>
            @endif
            <x-ui.button type="button" variant="soft" :href="route('tenant.invoices.index')">{{ __('View Invoices') }}</x-ui.button>

            <x-slot:panel>
                @include('tenant.revenue.partials.filter-panel', [
                    'filter' => $filter,
                    'filterOptions' => $filterOptions,
                ])
            </x-slot:panel>
        </x-tenant.list-toolbar>

        <div class="space-y-6">
        {{-- 2 & 3. Trend + Salespeople --}}
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2 lg:items-stretch">
            <section class="flex h-full flex-col overflow-hidden rounded-2xl border border-sky-100/80 bg-white shadow-sm shadow-sky-100/40">
                @include('tenant.revenue.partials.section-header', [
                    'title' => __('Revenue Trend'),
                    'subtitle' => __('Revenue by month'),
                    'headerClass' => 'border-sky-100/80 bg-gradient-to-r from-sky-50/80 to-white',
                ])
                <div class="flex flex-1 px-5 py-5 sm:px-6 sm:py-6">
                    @include('tenant.revenue.partials.trend-chart', ['points' => $trend])
                </div>
            </section>

            <section class="flex h-full flex-col overflow-hidden rounded-2xl border border-violet-100/80 bg-white shadow-sm shadow-violet-100/40">
                @include('tenant.revenue.partials.section-header', [
                    'title' => __('Top Performing Salespeople'),
                    'subtitle' => __('Ranked by revenue, bookings, or sales value'),
                    'headerClass' => 'border-violet-100/80 bg-gradient-to-r from-violet-50/70 to-white',
                ])
                <div class="flex flex-1 px-5 py-5 sm:px-6 sm:py-6">
                    @include('tenant.revenue.partials.salespeople-chart', [
                        'people' => $topSalespeople,
                        'maxValues' => $salespeopleMaxValues,
                    ])
                </div>
            </section>
        </div>

        {{-- 4. Revenue by Project --}}
        <section class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm">
            @include('tenant.revenue.partials.section-header', [
                'title' => __('Revenue by Project'),
                'subtitle' => __('Performance breakdown across projects'),
                'headerClass' => 'border-slate-200/80 bg-gradient-to-r from-slate-50 to-white',
            ])

            <div class="overflow-x-auto px-5 pb-5 pt-2 sm:px-6 sm:pb-6">
                <table class="min-w-full divide-y divide-slate-100">
                    <thead class="bg-slate-50/80">
                        <tr class="align-middle">
                            <th class="whitespace-nowrap px-4 py-3.5 text-start align-middle text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Project') }}</th>
                            <th class="whitespace-nowrap px-4 py-3.5 text-start align-middle text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Bookings') }}</th>
                            <th class="whitespace-nowrap px-4 py-3.5 text-start align-middle text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Sales Value') }}</th>
                            <th class="whitespace-nowrap px-4 py-3.5 text-start align-middle text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Total Commission') }}</th>
                            <th class="whitespace-nowrap px-4 py-3.5 text-start align-middle text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Pending Commission') }}</th>
                            <th class="whitespace-nowrap px-4 py-3.5 text-start align-middle text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Received Commission') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse ($projects as $project)
                            <tr class="transition-colors hover:bg-slate-50/70">
                                <td class="whitespace-nowrap px-4 py-3.5 align-middle text-sm font-medium text-black">
                                    {{ $project['project'] }}
                                    @if ($project['developer'])
                                        <p class="mt-1 text-xs font-normal leading-relaxed text-violet-600/80">{{ $project['developer'] }}</p>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap px-4 py-3.5 align-middle text-sm text-slate-600">{{ number_format($project['bookings_count']) }}</td>
                                <td class="whitespace-nowrap px-4 py-3.5 align-middle text-sm font-medium text-sky-700">₹{{ number_format($project['sales_value']) }}</td>
                                <td class="whitespace-nowrap px-4 py-3.5 align-middle text-sm text-slate-700">₹{{ number_format($project['total_commission']) }}</td>
                                <td class="whitespace-nowrap px-4 py-3.5 align-middle text-sm font-medium text-amber-700">₹{{ number_format($project['pending_commission']) }}</td>
                                <td class="whitespace-nowrap px-4 py-3.5 align-middle text-sm font-medium text-emerald-700">₹{{ number_format($project['received_commission']) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-14 text-center text-sm text-slate-500">
                                    {{ __('No project revenue data for the selected filters.') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
        </div>
    </div>
</x-tenant-layout>
