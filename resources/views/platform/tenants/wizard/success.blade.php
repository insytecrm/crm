<x-app-layout :title="__('Channel Partner Created') . ' | InSyte CRM'">
    <div class="mx-auto max-w-lg py-10 text-center">
        <div class="mx-auto mb-4 flex size-14 items-center justify-center rounded-full bg-emerald-50 text-2xl text-emerald-600">✓</div>
        <h1 class="text-2xl font-bold tracking-tight text-black">{{ __('Channel Partner Created') }}</h1>
        <p class="mt-2 text-sm text-slate-500">
            {{ __(':name is ready to use InSyte.', ['name' => $tenant->name]) }}
        </p>

        <div class="mt-8 flex flex-col items-center justify-center gap-3 sm:flex-row">
            <x-ui.button variant="default" :href="route('tenants.show', $tenant)">
                {{ __('Go to Partner') }}
            </x-ui.button>
            <x-ui.button variant="outline" :href="route('tenants.index')">
                {{ __('Back to Channel Partners') }}
            </x-ui.button>
        </div>
    </div>
</x-app-layout>
