<x-app-layout :title="__('Create Plan') . ' | InSyte CRM'">
    <x-platform.page-header
        :title="__('Create Plan')"
        :description="__('Step 3 of 5 — Features')"
    >
        <x-slot:actions>
            <x-ui.button variant="outline" :href="route('platform.plans.wizard.pricing')">{{ __('Back') }}</x-ui.button>
        </x-slot:actions>
    </x-platform.page-header>

    <x-platform.plan-wizard-steps :step="$step" />

    <form method="POST" action="{{ route('platform.plans.wizard.features.store') }}" class="mx-auto max-w-2xl space-y-4">
        @csrf
        <x-platform.panel :title="__('Choose Features')">
            @include('platform.plans.partials.feature-fields', [
                'modules' => $modules,
                'integrations' => $integrations,
                'packs' => $packs,
                'capabilitiesByFeature' => $capabilitiesByFeature,
                'plan' => null,
                'draft' => $draft,
            ])
            <div class="mt-6 flex justify-end gap-2">
                <x-ui.button variant="outline" :href="route('platform.plans.wizard.pricing')">{{ __('Back') }}</x-ui.button>
                <x-ui.button type="submit" variant="default">{{ __('Continue') }}</x-ui.button>
            </div>
        </x-platform.panel>
    </form>

    @include('platform.plans.partials.preset-editor', ['presets' => $presets])
</x-app-layout>
