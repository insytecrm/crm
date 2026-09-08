<x-app-layout :title="$title . ' | InSyte CRM'">
    <x-platform.page-header
        :title="$title"
        :description="$description"
    />

    <x-platform.panel :title="__('Coming soon')">
        <p class="text-sm text-slate-500">
            {{ __('This section is a stub. We will build it out next.') }}
        </p>

        @if (request()->routeIs('platform.settings'))
            <div class="mt-4">
                <x-ui.button variant="outline" :href="route('profile.edit')">
                    {{ __('Open profile') }}
                </x-ui.button>
            </div>
        @endif
    </x-platform.panel>
</x-app-layout>
