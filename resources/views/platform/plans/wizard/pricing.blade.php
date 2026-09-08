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

    <form method="POST" action="{{ route('platform.plans.wizard.pricing.store') }}" class="mx-auto max-w-2xl" x-data="{ trial: {{ old('trial_enabled', $draft['trial_enabled'] ?? true) ? 'true' : 'false' }} }">
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
                <input type="checkbox" name="trial_enabled" value="1" class="rounded border-slate-300 text-navy focus:ring-navy" x-model="trial" @checked(old('trial_enabled', $draft['trial_enabled'] ?? true))>
                {{ __('Enable free trial') }}
            </label>
            <div class="mt-3" x-show="trial" x-cloak>
                <x-input-label for="trial_days" :value="__('Trial Duration')" />
                <x-text-input id="trial_days" name="trial_days" type="number" min="1" max="90" class="mt-1 block w-32" :value="old('trial_days', $draft['trial_days'] ?? 7)" />
                <p class="mt-1 text-xs text-slate-400">{{ __('Days') }}</p>
            </div>
            <div class="mt-6 flex justify-end gap-2">
                <x-ui.button variant="outline" :href="route('platform.plans.wizard.basic')">{{ __('Back') }}</x-ui.button>
                <x-ui.button type="submit" variant="default">{{ __('Continue') }}</x-ui.button>
            </div>
        </x-platform.panel>
    </form>
</x-app-layout>
