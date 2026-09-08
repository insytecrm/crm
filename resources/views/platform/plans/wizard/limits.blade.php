<x-app-layout :title="__('Create Plan') . ' | InSyte CRM'">
    <x-platform.page-header
        :title="__('Create Plan')"
        :description="__('Step 4 of 5 — Limits')"
    >
        <x-slot:actions>
            <x-ui.button variant="outline" :href="route('platform.plans.wizard.features')">{{ __('Back') }}</x-ui.button>
        </x-slot:actions>
    </x-platform.page-header>

    <x-platform.plan-wizard-steps :step="$step" />

    <form method="POST" action="{{ route('platform.plans.wizard.limits.store') }}" class="mx-auto max-w-2xl">
        @csrf
        <x-platform.panel :title="__('Set Usage Limits')">
            @include('platform.plans.partials.limit-fields', [
                'limits' => $limits,
                'plan' => null,
                'draft' => $draft,
            ])
            <div class="mt-6 flex justify-end gap-2">
                <x-ui.button variant="outline" :href="route('platform.plans.wizard.features')">{{ __('Back') }}</x-ui.button>
                <x-ui.button type="submit" variant="default">{{ __('Continue') }}</x-ui.button>
            </div>
        </x-platform.panel>
    </form>
</x-app-layout>
