<x-app-layout :title="__('Plans') . ' | InSyte CRM'">
    <x-platform.page-header
        :title="__('Plans')"
        :description="__('Manage the plans and pricing available to Channel Partners.')"
    >
        <x-slot:actions>
            <x-ui.button variant="default" :href="route('platform.plans.create')">
                {{ __('+ Create Plan') }}
            </x-ui.button>
        </x-slot:actions>
    </x-platform.page-header>

    <x-auth-session-status class="mb-4 rounded-xl border border-emerald-100 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700" :status="session('status')" />

    @if ($plans->isEmpty())
        <x-platform.panel>
            <p class="text-sm text-slate-500">{{ __('No plans yet. Create the first plan Channel Partners can subscribe to.') }}</p>
        </x-platform.panel>
    @else
        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            @foreach ($plans as $plan)
                <x-platform.panel compact class="relative">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <a href="{{ route('platform.plans.show', $plan) }}" class="text-lg font-semibold text-black hover:text-navy">
                                {{ $plan->name }}
                            </a>
                            <p class="mt-1 text-sm text-slate-500">{{ $plan->description ?: __('No description') }}</p>
                        </div>
                        <x-ui.popover side="bottom" align="end" width="44" content-class="p-1" close-on-content-click>
                            <x-slot:trigger>
                                <button
                                    type="button"
                                    class="inline-flex size-8 items-center justify-center rounded-lg text-slate-500 hover:bg-slate-50"
                                    aria-label="{{ __('Plan actions') }}"
                                >
                                    <svg class="size-4" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <circle cx="5" cy="12" r="1.6" />
                                        <circle cx="12" cy="12" r="1.6" />
                                        <circle cx="19" cy="12" r="1.6" />
                                    </svg>
                                </button>
                            </x-slot:trigger>
                            <x-ui.popover.item :href="route('platform.plans.show', $plan)">{{ __('View') }}</x-ui.popover.item>
                            <x-ui.popover.item :href="route('platform.plans.edit', $plan)">{{ __('Edit') }}</x-ui.popover.item>
                            <x-ui.popover.item :href="route('platform.plans.duplicate', $plan)">{{ __('Duplicate') }}</x-ui.popover.item>
                            @if (! $plan->isArchived())
                                <form method="POST" action="{{ route('platform.plans.archive', $plan) }}" onsubmit="return confirm(@js(__('Archive :name? Existing Channel Partners stay on this plan.', ['name' => $plan->name])))">
                                    @csrf
                                    <button type="submit" class="flex w-full items-center rounded-md px-2 py-1.5 text-sm font-medium text-rose-600 hover:bg-rose-50">
                                        {{ __('Archive') }}
                                    </button>
                                </form>
                            @endif
                        </x-ui.popover>
                    </div>

                    <p class="mt-4 text-2xl font-bold tracking-tight text-black">
                        {{ $plan->monthlyPriceLabel() }}
                        <span class="text-sm font-medium text-slate-500">{{ __(' / month') }}</span>
                    </p>
                    <p class="mt-1 text-sm text-slate-500">{{ $plan->annualPriceLabel() }} {{ __(' / year') }}</p>

                    <dl class="mt-4 space-y-2 text-sm">
                        <div class="flex justify-between gap-3">
                            <dt class="text-slate-500">{{ __('Trial') }}</dt>
                            <dd class="font-medium text-black">{{ $plan->trialLabel() }}</dd>
                        </div>
                        <div class="flex justify-between gap-3">
                            <dt class="text-slate-500">{{ __('Active Partners') }}</dt>
                            <dd class="font-medium text-black">{{ number_format($plan->partners_count) }}</dd>
                        </div>
                        <div class="flex justify-between gap-3">
                            <dt class="text-slate-500">{{ __('Status') }}</dt>
                            <dd><x-platform.status-badge :status="$plan->status" /></dd>
                        </div>
                    </dl>
                </x-platform.panel>
            @endforeach
        </div>
    @endif
</x-app-layout>
