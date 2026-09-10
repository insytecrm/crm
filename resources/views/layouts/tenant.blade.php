@php
    use App\Enums\PlanCapability;
    use App\Enums\PlanFeature;
    use App\Enums\TenantPermission;
@endphp

<x-sidebar.shell
    :title="$title ?? null"
    :logout-action="route('tenant.logout')"
    :full-bleed="$fullBleed ?? false"
>
    <x-slot:actions>
        <x-sidebar.header-actions />
    </x-slot:actions>

    <x-slot:navigation>
        <x-tenant.can :permission="TenantPermission::DashboardView">
            <x-sidebar.link :href="route('tenant.dashboard')" :active="request()->routeIs('tenant.dashboard')">
            <x-slot:icon>
                <x-sidebar.nav-icon name="dashboard" />
            </x-slot:icon>
            {{ __('Dashboard') }}
            </x-sidebar.link>
        </x-tenant.can>

        <x-tenant.can :permission="TenantPermission::AiUse" :feature="PlanFeature::InsyteAi->value">
            <x-sidebar.link :href="route('tenant.ai.index')" :active="request()->routeIs('tenant.ai.*')">
            <x-slot:icon>
                <x-sidebar.nav-icon name="ai" />
            </x-slot:icon>
            {{ __('InSyte AI OS') }}
            </x-sidebar.link>
        </x-tenant.can>

        <x-tenant.can :permission="TenantPermission::LeadsView" :feature="PlanFeature::Crm->value">
        <x-sidebar.group
            :href="route('tenant.leads.index')"
            :active="request()->routeIs('tenant.leads.index', 'tenant.leads.priority.*', 'tenant.leads.unassigned.*', 'tenant.leads.converted.*', 'tenant.leads.lost.*', 'tenant.leads.duplicates.*')"
            :indicator="data_get($navIndicators, 'unassigned_leads', 0) > 0 ? data_get($navIndicators, 'unassigned_leads') : null"
        >
            <x-slot:icon>
                <x-sidebar.nav-icon name="leads" />
            </x-slot:icon>
            {{ __('Leads') }}

            <x-slot:submenu>
                <x-sidebar.sublink :href="route('tenant.leads.priority.index')" :active="request()->routeIs('tenant.leads.priority.*')">
                    <x-slot:icon>
                        <x-sidebar.nav-icon name="priority" />
                    </x-slot:icon>
                    {{ __('Priority Leads') }}
                </x-sidebar.sublink>

                <x-sidebar.sublink :href="route('tenant.leads.converted.index')" :active="request()->routeIs('tenant.leads.converted.*')">
                    <x-slot:icon>
                        <x-sidebar.nav-icon name="converted" />
                    </x-slot:icon>
                    {{ __('Converted Leads') }}
                </x-sidebar.sublink>

                <x-sidebar.sublink :href="route('tenant.leads.lost.index')" :active="request()->routeIs('tenant.leads.lost.*')">
                    <x-slot:icon>
                        <x-sidebar.nav-icon name="lost" />
                    </x-slot:icon>
                    {{ __('Lost Leads') }}
                </x-sidebar.sublink>

                <x-tenant.can :capability="PlanCapability::CrmDuplicates->value">
                    <x-sidebar.sublink :href="route('tenant.leads.duplicates.index')" :active="request()->routeIs('tenant.leads.duplicates.*')">
                        <x-slot:icon>
                            <x-sidebar.nav-icon name="duplicates" />
                        </x-slot:icon>
                        {{ __('Duplicate Leads') }}
                    </x-sidebar.sublink>
                </x-tenant.can>
            </x-slot:submenu>
        </x-sidebar.group>
        </x-tenant.can>

        <x-tenant.can :permission="TenantPermission::ActivitiesView" :feature="PlanFeature::Crm->value">
        <x-sidebar.group
            :href="route('tenant.activities.index')"
            :active="request()->routeIs('tenant.activities.*', 'tenant.follow-ups.*', 'tenant.site-visits.*')"
            :indicator="data_get($navIndicators, 'overdue_activities', 0) > 0 ? data_get($navIndicators, 'overdue_activities') : null"
        >
            <x-slot:icon>
                <x-sidebar.nav-icon name="activities" />
            </x-slot:icon>
            {{ __('Activities') }}

            <x-slot:submenu>
                <x-sidebar.sublink :href="route('tenant.follow-ups.index')" :active="request()->routeIs('tenant.follow-ups.*')">
                    <x-slot:icon>
                        <x-sidebar.nav-icon name="follow-ups" />
                    </x-slot:icon>
                    {{ __('Follow-Ups') }}
                </x-sidebar.sublink>

                <x-sidebar.sublink :href="route('tenant.site-visits.index')" :active="request()->routeIs('tenant.site-visits.*')">
                    <x-slot:icon>
                        <x-sidebar.nav-icon name="site-visits" />
                    </x-slot:icon>
                    {{ __('Site Visits') }}
                </x-sidebar.sublink>
            </x-slot:submenu>
        </x-sidebar.group>
        </x-tenant.can>

        <x-tenant.can :permission="TenantPermission::TasksView" :feature="PlanFeature::Crm->value">
        <x-sidebar.link :href="route('tenant.tasks.index')" :active="request()->routeIs('tenant.tasks.*')">
            <x-slot:icon>
                <x-sidebar.nav-icon name="tasks" />
            </x-slot:icon>
            {{ __('Tasks') }}
        </x-sidebar.link>
        </x-tenant.can>

        <x-tenant.can :permission="TenantPermission::LeadsView" :feature="PlanFeature::WhatsApp->value">
        <x-sidebar.link :href="route('tenant.whatsapp-web.index')" :active="request()->routeIs('tenant.whatsapp-web.*')">
            <x-slot:icon>
                <x-sidebar.nav-icon name="whatsapp" />
            </x-slot:icon>
            {{ __('WhatsApp Web') }}
        </x-sidebar.link>
        </x-tenant.can>

        <x-sidebar.separator />

        <x-tenant.can :permission="TenantPermission::PropertiesView" :feature="PlanFeature::Properties->value">
        <x-sidebar.link :href="route('tenant.properties.index')" :active="request()->routeIs('tenant.properties.*')">
            <x-slot:icon>
                <x-sidebar.nav-icon name="properties" />
            </x-slot:icon>
            {{ __('Properties') }}
        </x-sidebar.link>
        </x-tenant.can>

        <x-tenant.can :permission="TenantPermission::BookingsView" :feature="PlanFeature::Bookings->value">
        <x-sidebar.link :href="route('tenant.bookings.index')" :active="request()->routeIs('tenant.bookings.*')">
            <x-slot:icon>
                <x-sidebar.nav-icon name="bookings" />
            </x-slot:icon>
            {{ __('Bookings') }}
        </x-sidebar.link>
        </x-tenant.can>

        <x-tenant.can :permission="TenantPermission::RevenueView" :feature="PlanFeature::Revenue->value">
        <x-sidebar.group
            :href="route('tenant.revenue.index')"
            :active="request()->routeIs('tenant.revenue.*', 'tenant.invoices.*', 'tenant.payouts.*')"
        >
            <x-slot:icon>
                <x-sidebar.nav-icon name="revenue" />
            </x-slot:icon>
            {{ __('Revenue') }}

            <x-slot:submenu>
                <x-sidebar.sublink :href="route('tenant.invoices.index')" :active="request()->routeIs('tenant.invoices.*', 'tenant.payouts.*')">
                    <x-slot:icon>
                        <x-sidebar.nav-icon name="invoices" />
                    </x-slot:icon>
                    {{ __('Invoices') }}
                </x-sidebar.sublink>
            </x-slot:submenu>
        </x-sidebar.group>
        </x-tenant.can>

        <x-sidebar.separator />

        <x-tenant.can :permission="TenantPermission::ReportsView" :feature="PlanFeature::Reports->value">
        <x-sidebar.group
            :href="route('tenant.reports.index')"
            :active="request()->routeIs('tenant.reports.*')"
        >
            <x-slot:icon>
                <x-sidebar.nav-icon name="reports" />
            </x-slot:icon>
            {{ __('Reports') }}

            <x-slot:submenu>
                <x-tenant.can :capability="PlanCapability::ReportsAnalytics->value">
                    <x-sidebar.sublink :href="route('tenant.reports.analytics')" :active="request()->routeIs('tenant.reports.analytics')">
                        <x-slot:icon>
                            <x-sidebar.nav-icon name="analytics" />
                        </x-slot:icon>
                        {{ __('Analytics') }}
                    </x-sidebar.sublink>
                </x-tenant.can>
            </x-slot:submenu>
        </x-sidebar.group>
        </x-tenant.can>

        <x-tenant.can :permission="TenantPermission::TeamsView" :feature="PlanFeature::Teams->value">
        <x-sidebar.group
            :href="route('tenant.teams.index')"
            :active="request()->routeIs('tenant.teams.*')"
        >
            <x-slot:icon>
                <x-sidebar.nav-icon name="teams" />
            </x-slot:icon>
            {{ __('Teams') }}

            <x-slot:submenu>
                <x-sidebar.sublink :href="route('tenant.teams.performance.index')" :active="request()->routeIs('tenant.teams.performance.*')">
                    <x-slot:icon>
                        <x-sidebar.nav-icon name="performance" />
                    </x-slot:icon>
                    {{ __('Performance') }}
                </x-sidebar.sublink>
            </x-slot:submenu>
        </x-sidebar.group>
        </x-tenant.can>

        <x-sidebar.separator />

        <x-tenant.can :permission="TenantPermission::AutomationsView" :feature="PlanFeature::Automations->value">
        <x-sidebar.group
            :href="route('tenant.automations.index')"
            :active="request()->routeIs('tenant.automations.*')"
        >
            <x-slot:icon>
                <x-sidebar.nav-icon name="automations" />
            </x-slot:icon>
            {{ __('Automations') }}

            <x-slot:submenu>
                <x-sidebar.sublink :href="route('tenant.automations.workflows')" :active="request()->routeIs('tenant.automations.workflows')">
                    <x-slot:icon>
                        <x-sidebar.nav-icon name="workflows" />
                    </x-slot:icon>
                    {{ __('Workflows') }}
                </x-sidebar.sublink>
                <x-tenant.can :capability="PlanCapability::AutomationsTemplates->value">
                    <x-sidebar.sublink :href="route('tenant.automations.templates')" :active="request()->routeIs('tenant.automations.templates*')">
                        <x-slot:icon>
                            <x-sidebar.nav-icon name="templates" />
                        </x-slot:icon>
                        {{ __('Templates') }}
                    </x-sidebar.sublink>
                </x-tenant.can>
            </x-slot:submenu>
        </x-sidebar.group>
        </x-tenant.can>

    </x-slot:navigation>

    <x-slot:footer>
        <x-sidebar.link :href="route('tenant.settings.index')" :active="request()->routeIs('tenant.settings.*')">
            <x-slot:icon>
                <x-sidebar.nav-icon name="settings" />
            </x-slot:icon>
            {{ __('Settings') }}
        </x-sidebar.link>
    </x-slot:footer>

    {{ $slot }}

    @include('tenant.partials.external-redirect')

    @push('modals')
        @include('tenant.leads.partials.send-whatsapp-modal')
    @endpush

    @push('drawers')
        @include('tenant.leads.partials.drawer-host')
        {{ $drawers ?? '' }}
    @endpush
</x-sidebar.shell>
