<x-sidebar.shell
    :title="$title ?? null"
    :logout-action="route('logout')"
    :profile-href="route('profile.edit')"
>
    <x-slot:navigation>
        <x-sidebar.link :href="route('platform.dashboard')" :active="request()->routeIs('platform.dashboard')">
            <x-slot:icon>
                <x-sidebar.nav-icon name="dashboard" />
            </x-slot:icon>
            {{ __('Dashboard') }}
        </x-sidebar.link>

        <x-sidebar.link :href="route('tenants.index')" :active="request()->routeIs('tenants.*')">
            <x-slot:icon>
                <x-sidebar.nav-icon name="partners" />
            </x-slot:icon>
            {{ __('Channel Partners') }}
        </x-sidebar.link>

        <x-sidebar.separator />

        <x-sidebar.link :href="route('platform.plans')" :active="request()->routeIs('platform.plans', 'platform.plans.*')">
            <x-slot:icon>
                <x-sidebar.nav-icon name="plans" />
            </x-slot:icon>
            {{ __('Plans') }}
        </x-sidebar.link>

        <x-sidebar.link :href="route('platform.revenue')" :active="request()->routeIs('platform.revenue', 'platform.revenue.*')">
            <x-slot:icon>
                <x-sidebar.nav-icon name="revenue" />
            </x-slot:icon>
            {{ __('Revenue & Billing') }}
        </x-sidebar.link>

        <x-sidebar.link :href="route('platform.quotations')" :active="request()->routeIs('platform.quotations', 'platform.quotations.*')">
            <x-slot:icon>
                <x-sidebar.nav-icon name="quotations" />
            </x-slot:icon>
            {{ __('Quotations') }}
        </x-sidebar.link>

        <x-sidebar.separator />

        <x-sidebar.link :href="route('platform.integrations')" :active="request()->routeIs('platform.integrations')">
            <x-slot:icon>
                <x-sidebar.nav-icon name="integrations" />
            </x-slot:icon>
            {{ __('Integrations') }}
        </x-sidebar.link>

        <x-sidebar.link :href="route('platform.analytics')" :active="request()->routeIs('platform.analytics')">
            <x-slot:icon>
                <x-sidebar.nav-icon name="analytics" />
            </x-slot:icon>
            {{ __('Analytics') }}
        </x-sidebar.link>

        <x-sidebar.link :href="route('platform.utilities')" :active="request()->routeIs('platform.utilities')">
            <x-slot:icon>
                <x-sidebar.nav-icon name="utilities" />
            </x-slot:icon>
            {{ __('Utilities') }}
        </x-sidebar.link>
    </x-slot:navigation>

    <x-slot:footer>
        <x-sidebar.link :href="route('platform.settings')" :active="request()->routeIs('platform.settings', 'profile.*')">
            <x-slot:icon>
                <x-sidebar.nav-icon name="settings" />
            </x-slot:icon>
            {{ __('Settings') }}
        </x-sidebar.link>
    </x-slot:footer>

    {{ $slot }}
</x-sidebar.shell>
