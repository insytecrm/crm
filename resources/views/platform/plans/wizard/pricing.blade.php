<x-app-layout :title="__('Create Plan') . ' | InSyte CRM'">
    <x-platform.page-header
        :title="__('Create Plan')"
        :description="__('Step 2 of 5 — Pricing')"
    >
        <x-slot:actions>
            <x-ui.button variant="outline" :href="route('platform.plans.wizard.basic')">{{ __('Back') }}</x-ui.button>
        </x-slot:actions>
    </x-platform.page-header>

    <x-platform.plan-wizard-steps :step="$step" />

    <form method="POST" action="{{ route('platform.plans.wizard.pricing.store') }}" class="mx-auto max-w-2xl">
        @csrf
        <x-platform.panel :title="__('Pricing')">
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <x-input-label for="price_monthly" :value="__('Monthly Price')" />
                    <x-text-input id="price_monthly" name="price_monthly" type="number" min="0" class="mt-1 block w-full" :value="old('price_monthly', $draft['price_monthly'] ?? '')" required />
                    <x-input-error class="mt-2" :messages="$errors->get('price_monthly')" />
                </div>
                <div>
                    <x-input-label for="price_annual" :value="__('Annual Price')" />
                    <x-text-input id="price_annual" name="price_annual" type="number" min="0" class="mt-1 block w-full" :value="old('price_annual', $draft['price_annual'] ?? '')" required />
                    <x-input-error class="mt-2" :messages="$errors->get('price_annual')" />
                </div>
            </div>
            <label class="mt-5 flex items-center gap-3 text-sm font-medium text-black">
                <input type="checkbox" name="trial_enabled" value="1" class="rounded border-slate-300 text-navy focus:ring-navy" @checked(old('trial_enabled', $draft['trial_enabled'] ?? true))>
                {{ __('Allow this plan for trials (duration set when starting trial on a lead)') }}
            </label>
            <div class="mt-6 flex justify-end gap-2">
                <x-ui.button variant="outline" :href="route('platform.plans.wizard.basic')">{{ __('Back') }}</x-ui.button>
                <x-ui.button type="submit" variant="default">{{ __('Continue') }}</x-ui.button>
            </div>
        </x-platform.panel>
    </form>
</x-app-layout>
