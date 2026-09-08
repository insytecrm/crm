@php
    use App\Enums\TenantPermission;
@endphp

<x-tenant-layout :title="__('Dashboard') . ' | InSyte CRM'">
    <div class="mb-5">
        <h1 class="text-2xl font-bold tracking-tight text-black">
            {{ __('Welcome, :name', ['name' => auth()->user()->name]) }}
        </h1>
    </div>

    <div class="flex gap-2">
        <x-tenant.stat-card
            comfortable
            :label="__('Total Leads')"
            :value="number_format($kpis['total_leads'])"
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
            :label="__('Active Leads')"
            :value="number_format($kpis['active_leads'])"
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
            :label="__('Site Visits')"
            :value="number_format($kpis['site_visits'])"
            accent="emerald"
            :href="route('tenant.activities.index', ['kind' => 'site_visit'])"
        >
            <x-slot:icon>
                <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                </svg>
            </x-slot:icon>
        </x-tenant.stat-card>

        <x-tenant.stat-card
            comfortable
            :label="__('Bookings')"
            :value="number_format($kpis['bookings'])"
            accent="amber"
            :href="route('tenant.bookings.index')"
        >
            <x-slot:icon>
                <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 6v.75m0 3v.75m0 3v.75m-13.125 3h18.375A2.625 2.625 0 0 0 21 18.375V5.625A2.625 2.625 0 0 0 18.375 3H5.625A2.625 2.625 0 0 0 3 5.625v12.75A2.625 2.625 0 0 0 5.625 21Z" />
                </svg>
            </x-slot:icon>
        </x-tenant.stat-card>

        <x-tenant.can :permission="TenantPermission::RevenueView">
            <x-tenant.stat-card
                comfortable
                :label="__('Sales')"
                :value="'₹' . number_format($kpis['revenue'])"
                accent="emerald"
                :href="route('tenant.revenue.index')"
            >
                <x-slot:icon>
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                </x-slot:icon>
            </x-tenant.stat-card>
        </x-tenant.can>
    </div>

    {{-- Pipeline + Today's Tasks (KPI 1–3 width) | My Day (remaining = KPI 4–5) --}}
    <div class="tenant-dashboard-panels mt-4 flex items-stretch gap-2">
        <div class="flex min-w-0 shrink-0 flex-col gap-2" style="width: calc((100% - 2rem) * 3 / 5 + 1rem)">
            @include('tenant.dashboard.partials.pipeline-card', [
                'pipeline' => $pipeline,
                'periodFilter' => $periodFilter,
            ])
            @include('tenant.dashboard.partials.todays-tasks-card', [
                'tasks' => $todaysTasks,
            ])
        </div>
        <div class="min-w-0 flex-1">
            @include('tenant.dashboard.partials.my-day-card', [
                'activities' => $myDayActivities,
            ])
        </div>
    </div>
</x-tenant-layout>
