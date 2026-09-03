@php
    use App\Enums\SettingsTab;
@endphp

<x-tenant-layout :title="__('Settings') . ' | InSyte CRM'">
    @if (session('status'))
        <div class="mb-4 rounded-xl border border-emerald-100 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
            {{ session('status') }}
        </div>
    @endif

    <div class="overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-sm">
        <x-ui.tabs :default="$tab->value">
            <x-ui.tabs.tab-list class="mx-4 mt-4 w-auto sm:mx-6 sm:mt-6">
                @foreach ($visibleTabs as $settingsTab)
                    <x-ui.tabs.tab-trigger value="{{ $settingsTab->value }}">{{ $settingsTab->label() }}</x-ui.tabs.tab-trigger>
                @endforeach
            </x-ui.tabs.tab-list>

            <div class="p-4 sm:p-6 lg:p-8">
                <x-ui.tabs.tab-content value="{{ SettingsTab::Profile->value }}" :default="$tab->value" class="space-y-6">
                    @include('tenant.settings.partials.profile-tab')
                </x-ui.tabs.tab-content>

                @if (in_array(SettingsTab::Company, $visibleTabs, true))
                    <x-ui.tabs.tab-content value="{{ SettingsTab::Company->value }}" :default="$tab->value" class="space-y-6">
                        @include('tenant.settings.partials.company-tab')
                    </x-ui.tabs.tab-content>
                @endif

                <x-ui.tabs.tab-content value="{{ SettingsTab::Security->value }}" :default="$tab->value" class="space-y-6">
                    @include('tenant.settings.partials.security-tab')
                </x-ui.tabs.tab-content>

                <x-ui.tabs.tab-content value="{{ SettingsTab::Notifications->value }}" :default="$tab->value" class="space-y-6">
                    @include('tenant.settings.partials.notifications-tab')
                </x-ui.tabs.tab-content>

                @if (in_array(SettingsTab::Users, $visibleTabs, true))
                    <x-ui.tabs.tab-content value="{{ SettingsTab::Users->value }}" :default="$tab->value" class="space-y-6">
                        @include('tenant.settings.partials.users-tab')
                    </x-ui.tabs.tab-content>
                @endif

                @if (in_array(SettingsTab::Roles, $visibleTabs, true))
                    <x-ui.tabs.tab-content value="{{ SettingsTab::Roles->value }}" :default="$tab->value" class="space-y-6">
                        @include('tenant.settings.partials.roles-tab')
                    </x-ui.tabs.tab-content>
                @endif
            </div>
        </x-ui.tabs>
    </div>
</x-tenant-layout>
