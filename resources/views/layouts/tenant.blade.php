@php
    use App\Enums\TenantPermission;
@endphp

<x-sidebar.shell
    :title="$title ?? null"
    :context-label="tenant('name')"
    :logout-action="route('tenant.logout')"
>
    <x-slot:actions>
        <x-sidebar.header-actions />
    </x-slot:actions>

    <x-slot:navigation>
        <x-tenant.can :permission="TenantPermission::DashboardView">
            <x-sidebar.link :href="route('tenant.dashboard')" :active="request()->routeIs('tenant.dashboard')">
            <x-slot:icon>
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" />
                </svg>
            </x-slot:icon>
            {{ __('Dashboard') }}
            </x-sidebar.link>
        </x-tenant.can>

        <x-tenant.can :permission="TenantPermission::LeadsView">
        <x-sidebar.group
            :href="route('tenant.leads.index')"
            :active="request()->routeIs('tenant.leads.index', 'tenant.leads.priority.*', 'tenant.leads.unassigned.*', 'tenant.leads.converted.*', 'tenant.leads.lost.*', 'tenant.leads.duplicates.*')"
        >
            <x-slot:icon>
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                </svg>
            </x-slot:icon>
            {{ __('Leads') }}

            <x-slot:submenu>
                <x-sidebar.sublink :href="route('tenant.leads.priority.index')" :active="request()->routeIs('tenant.leads.priority.*')">
                    {{ __('Priority Leads') }}
                </x-sidebar.sublink>

                <x-sidebar.sublink :href="route('tenant.leads.converted.index')" :active="request()->routeIs('tenant.leads.converted.*')">
                    {{ __('Converted Leads') }}
                </x-sidebar.sublink>

                <x-sidebar.sublink :href="route('tenant.leads.lost.index')" :active="request()->routeIs('tenant.leads.lost.*')">
                    {{ __('Lost Leads') }}
                </x-sidebar.sublink>

                <x-sidebar.sublink :href="route('tenant.leads.duplicates.index')" :active="request()->routeIs('tenant.leads.duplicates.*')">
                    {{ __('Duplicate Leads') }}
                </x-sidebar.sublink>
            </x-slot:submenu>
        </x-sidebar.group>
        </x-tenant.can>

        <x-tenant.can :permission="TenantPermission::ActivitiesView">
        <x-sidebar.group
            :href="route('tenant.activities.index')"
            :active="request()->routeIs('tenant.activities.*', 'tenant.follow-ups.*', 'tenant.site-visits.*')"
        >
            <x-slot:icon>
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                </svg>
            </x-slot:icon>
            {{ __('Activities') }}

            <x-slot:submenu>
                <x-sidebar.sublink :href="route('tenant.follow-ups.index')" :active="request()->routeIs('tenant.follow-ups.*')">
                    {{ __('Follow-Ups') }}
                </x-sidebar.sublink>

                <x-sidebar.sublink :href="route('tenant.site-visits.index')" :active="request()->routeIs('tenant.site-visits.*')">
                    {{ __('Site Visits') }}
                </x-sidebar.sublink>
            </x-slot:submenu>
        </x-sidebar.group>
        </x-tenant.can>

        <x-tenant.can :permission="TenantPermission::TasksView">
        <x-sidebar.link :href="route('tenant.tasks.index')" :active="request()->routeIs('tenant.tasks.*')">
            <x-slot:icon>
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
            </x-slot:icon>
            {{ __('Tasks') }}
        </x-sidebar.link>
        </x-tenant.can>

        <x-tenant.can :permission="TenantPermission::PropertiesView">
        <x-sidebar.link :href="route('tenant.properties.index')" :active="request()->routeIs('tenant.properties.*')">
            <x-slot:icon>
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" />
                </svg>
            </x-slot:icon>
            {{ __('Properties') }}
        </x-sidebar.link>
        </x-tenant.can>

        <x-tenant.can :permission="TenantPermission::BookingsView">
        <x-sidebar.link :href="route('tenant.bookings.index')" :active="request()->routeIs('tenant.bookings.*')">
            <x-slot:icon>
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 6v.75m0 3v.75m0 3v.75m-13.125 3h18.375A2.625 2.625 0 0 0 21 18.375V5.625A2.625 2.625 0 0 0 18.375 3H5.625A2.625 2.625 0 0 0 3 5.625v12.75A2.625 2.625 0 0 0 5.625 21Z" />
                </svg>
            </x-slot:icon>
            {{ __('Bookings') }}
        </x-sidebar.link>
        </x-tenant.can>

        <x-tenant.can :permission="TenantPermission::RevenueView">
        <x-sidebar.group
            :href="route('tenant.revenue.index')"
            :active="request()->routeIs('tenant.revenue.*', 'tenant.invoices.*', 'tenant.payouts.*')"
        >
            <x-slot:icon>
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
            </x-slot:icon>
            {{ __('Revenue') }}

            <x-slot:submenu>
                <x-sidebar.sublink :href="route('tenant.invoices.index')" :active="request()->routeIs('tenant.invoices.*', 'tenant.payouts.*')">
                    {{ __('Invoices') }}
                </x-sidebar.sublink>
            </x-slot:submenu>
        </x-sidebar.group>
        </x-tenant.can>

        <x-tenant.can :permission="TenantPermission::TeamsView">
        <x-sidebar.link :href="route('tenant.teams.index')" :active="request()->routeIs('tenant.teams.*')">
            <x-slot:icon>
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" />
                </svg>
            </x-slot:icon>
            {{ __('Teams') }}
        </x-sidebar.link>
        </x-tenant.can>

        <x-tenant.can :permission="TenantPermission::IntegrationsView">
        <x-sidebar.link :href="route('tenant.integrations.index')" :active="request()->routeIs('tenant.integrations.*')">
            <x-slot:icon>
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 0 1 1.242 7.244l-4.5 4.5a4.5 4.5 0 0 1-6.364-6.364l1.757-1.757m13.35-.622 1.757-1.757a4.5 4.5 0 0 0-6.364-6.364l-4.5 4.5a4.5 4.5 0 0 0 1.242 7.244" />
                </svg>
            </x-slot:icon>
            {{ __('Integrations') }}
        </x-sidebar.link>
        </x-tenant.can>
    </x-slot:navigation>

    <x-slot:footer>
        <x-sidebar.link :href="route('tenant.settings.index')" :active="request()->routeIs('tenant.settings.*')">
            <x-slot:icon>
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                </svg>
            </x-slot:icon>
            {{ __('Settings') }}
        </x-sidebar.link>
    </x-slot:footer>

    {{ $slot }}

    @push('modals')
        @include('tenant.partials.reminder-host')
    @endpush

    @push('drawers')
        @include('tenant.leads.partials.drawer-host')
    @endpush
</x-sidebar.shell>
