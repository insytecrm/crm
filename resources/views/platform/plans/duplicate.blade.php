<x-app-layout :title="__('Duplicate Plan') . ' | InSyte CRM'">
    <x-platform.page-header
        :title="__('Duplicate :name', ['name' => $plan->name])"
        :description="__('Create a new plan based on this one.')"
    >
        <x-slot:actions>
            <x-ui.button variant="outline" :href="route('platform.plans.show', $plan)">{{ __('Cancel') }}</x-ui.button>
        </x-slot:actions>
    </x-platform.page-header>

    <form method="POST" action="{{ route('platform.plans.duplicate.store', $plan) }}" class="mx-auto max-w-xl">
        @csrf
        <x-platform.panel>
            <div>
                <x-input-label for="name" :value="__('New Plan Name')" />
                <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $plan->name.' Plus')" required autofocus />
                <x-input-error class="mt-2" :messages="$errors->get('name')" />
            </div>

            <p class="mt-5 text-sm font-medium text-black">{{ __('Copy:') }}</p>
            <div class="mt-3 space-y-2 text-sm">
                <label class="flex items-center justify-between gap-3">
                    <span>{{ __('Pricing') }}</span>
                    <input type="checkbox" name="copy_pricing" value="1" class="rounded border-slate-300 text-navy focus:ring-navy" @checked(old('copy_pricing', true))>
                </label>
                <label class="flex items-center justify-between gap-3">
                    <span>{{ __('Features') }}</span>
                    <input type="checkbox" name="copy_features" value="1" class="rounded border-slate-300 text-navy focus:ring-navy" @checked(old('copy_features', true))>
                </label>
                <label class="flex items-center justify-between gap-3">
                    <span>{{ __('Limits') }}</span>
                    <input type="checkbox" name="copy_limits" value="1" class="rounded border-slate-300 text-navy focus:ring-navy" @checked(old('copy_limits', true))>
                </label>
                <label class="flex items-center justify-between gap-3">
                    <span>{{ __('Trial') }}</span>
                    <input type="checkbox" name="copy_trial" value="1" class="rounded border-slate-300 text-navy focus:ring-navy" @checked(old('copy_trial', true))>
                </label>
            </div>

            <div class="mt-6 flex justify-end gap-2">
                <x-ui.button variant="outline" :href="route('platform.plans.show', $plan)">{{ __('Cancel') }}</x-ui.button>
                <x-ui.button type="submit" variant="default">{{ __('Create Plan') }}</x-ui.button>
            </div>
        </x-platform.panel>
    </form>
</x-app-layout>
