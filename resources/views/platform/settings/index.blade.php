<x-app-layout :title="__('Settings') . ' | InSyte CRM'">
    <x-platform.page-header
        :title="__('Settings')"
        :description="__('Manage your super admin account, team access, and platform preferences.')"
    />

    <x-auth-session-status class="mb-4 rounded-xl border border-emerald-100 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700" :status="session('status')" />

    @if ($errors->any())
        <div class="mb-4 rounded-xl border border-rose-100 bg-rose-50 px-4 py-3 text-sm text-rose-700">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-sm">
        <x-ui.tabs :default="$tab === 'users' || $tab === 'roles' || $tab === 'permissions' ? 'team' : 'account'">
            <x-ui.tabs.tab-list class="mx-4 mt-4 w-auto sm:mx-6 sm:mt-6">
                <x-ui.tabs.tab-trigger value="account">{{ __('Account') }}</x-ui.tabs.tab-trigger>
                <x-ui.tabs.tab-trigger value="team">{{ __('Team') }}</x-ui.tabs.tab-trigger>
            </x-ui.tabs.tab-list>

            <div class="p-4 sm:p-6 lg:p-8">
                <x-ui.tabs.tab-content value="account" :default="$tab === 'users' || $tab === 'roles' || $tab === 'permissions' ? 'team' : 'account'" class="space-y-10">
                    @include('platform.settings.partials.profile')
                    @include('platform.settings.partials.security')
                </x-ui.tabs.tab-content>

                <x-ui.tabs.tab-content value="team" :default="$tab === 'users' || $tab === 'roles' || $tab === 'permissions' ? 'team' : 'account'" class="space-y-10">
                    @include('platform.settings.partials.users')
                    @include('platform.settings.partials.roles')
                    @include('platform.settings.partials.permissions')
                </x-ui.tabs.tab-content>
            </div>
        </x-ui.tabs>
    </div>
</x-app-layout>
