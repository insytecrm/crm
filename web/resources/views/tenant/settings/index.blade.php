@php
    use App\Enums\SettingsGroup;
    use App\Enums\SettingsTab;
@endphp

<x-tenant-layout :title="__('Settings') . ' | InSyte CRM'">
    @if (session('status'))
        <div class="mb-4 rounded-xl border border-emerald-100 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
            {{ session('status') }}
        </div>
    @endif

    <div class="overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-sm">
        <x-ui.tabs :default="$group->value">
            <x-ui.tabs.tab-list class="mx-4 mt-4 w-auto sm:mx-6 sm:mt-6">
                @foreach ($visibleGroups as $settingsGroup)
                    <x-ui.tabs.tab-trigger value="{{ $settingsGroup->value }}">{{ $settingsGroup->label() }}</x-ui.tabs.tab-trigger>
                @endforeach
            </x-ui.tabs.tab-list>

            <div class="p-4 sm:p-6 lg:p-8">
                @if (in_array(SettingsGroup::Account, $visibleGroups, true))
                    <x-ui.tabs.tab-content value="{{ SettingsGroup::Account->value }}" :default="$group->value" class="space-y-10">
                        @if (in_array(SettingsTab::Profile, $visibleTabs, true))
                            <section id="settings-profile">
                                @include('tenant.settings.partials.profile-tab')
                            </section>
                        @endif

                        @if (in_array(SettingsTab::Security, $visibleTabs, true))
                            <section id="settings-security" class="border-t border-slate-100 pt-10">
                                @include('tenant.settings.partials.security-tab')
                            </section>
                        @endif
                    </x-ui.tabs.tab-content>
                @endif

                @if (in_array(SettingsGroup::Workspace, $visibleGroups, true))
                    <x-ui.tabs.tab-content value="{{ SettingsGroup::Workspace->value }}" :default="$group->value" class="space-y-10">
                        @if (in_array(SettingsTab::Company, $visibleTabs, true))
                            <section id="settings-company">
                                @include('tenant.settings.partials.company-tab')
                            </section>
                        @endif

                        @if (in_array(SettingsTab::Domains, $visibleTabs, true))
                            <section id="settings-domains" @class(['border-t border-slate-100 pt-10' => in_array(SettingsTab::Company, $visibleTabs, true)])>
                                @include('tenant.settings.partials.domains-tab')
                            </section>
                        @endif
                    </x-ui.tabs.tab-content>
                @endif

                @if (in_array(SettingsGroup::Team, $visibleGroups, true))
                    @php
                        $teamTabs = SettingsGroup::Team->visibleTabs($visibleTabs);
                        $teamDefault = in_array($tab, $teamTabs, true)
                            ? $tab->value
                            : ($teamTabs[0]->value ?? SettingsTab::Users->value);
                    @endphp
                    <x-ui.tabs.tab-content value="{{ SettingsGroup::Team->value }}" :default="$group->value">
                        <x-ui.tabs :default="$teamDefault" class="gap-4">
                            @if (count($teamTabs) > 1)
                                <x-ui.tabs.tab-list class="w-auto">
                                    @foreach ($teamTabs as $teamTab)
                                        <x-ui.tabs.tab-trigger value="{{ $teamTab->value }}">{{ $teamTab->label() }}</x-ui.tabs.tab-trigger>
                                    @endforeach
                                </x-ui.tabs.tab-list>
                            @endif

                            @if (in_array(SettingsTab::Users, $visibleTabs, true))
                                <x-ui.tabs.tab-content value="{{ SettingsTab::Users->value }}" :default="$teamDefault" class="space-y-6" id="settings-users">
                                    @include('tenant.settings.partials.users-tab')
                                </x-ui.tabs.tab-content>
                            @endif

                            @if (in_array(SettingsTab::Roles, $visibleTabs, true))
                                <x-ui.tabs.tab-content value="{{ SettingsTab::Roles->value }}" :default="$teamDefault" class="space-y-6" id="settings-roles">
                                    @include('tenant.settings.partials.roles-tab')
                                </x-ui.tabs.tab-content>
                            @endif
                        </x-ui.tabs>
                    </x-ui.tabs.tab-content>
                @endif

                @if (in_array(SettingsGroup::Integrations, $visibleGroups, true))
                    <x-ui.tabs.tab-content value="{{ SettingsGroup::Integrations->value }}" :default="$group->value" class="space-y-6" id="settings-integrations">
                        @include('tenant.settings.partials.integrations-tab')
                    </x-ui.tabs.tab-content>
                @endif
            </div>
        </x-ui.tabs>
    </div>

    @if (in_array($tab, [SettingsTab::Security, SettingsTab::Domains, SettingsTab::Roles], true))
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                document.getElementById(@js('settings-'.$tab->value))?.scrollIntoView({ behavior: 'smooth', block: 'start' });
            });
        </script>
    @endif
</x-tenant-layout>
