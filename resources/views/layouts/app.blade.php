<x-sidebar.shell
    :title="$title ?? null"
    :context-label="__('Platform')"
    context-badge="Admin"
    :logout-action="route('logout')"
    :profile-href="route('profile.edit')"
>
    <x-slot:navigation>
        <x-sidebar.link :href="route('tenants.index')" :active="request()->routeIs('tenants.*')">
            <x-slot:icon>
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" />
                </svg>
            </x-slot:icon>
            {{ __('Companies') }}
        </x-sidebar.link>

        <x-sidebar.link :href="route('profile.edit')" :active="request()->routeIs('profile.*')">
            <x-slot:icon>
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.375 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                </svg>
            </x-slot:icon>
            {{ __('Profile') }}
        </x-sidebar.link>
    </x-slot:navigation>

    {{ $slot }}
</x-sidebar.shell>
