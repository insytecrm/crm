@props([
    'plan',
    'shell',
])

<div class="mb-4">
    <a href="{{ route('platform.plans') }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-slate-500 transition-colors hover:text-navy">
        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
        </svg>
        {{ __('Plans') }}
    </a>
</div>

<div class="mb-5 flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
    <div class="min-w-0">
        <h1 class="text-2xl font-bold tracking-tight text-black">{{ $shell['name'] }}</h1>
        <p class="mt-1 text-sm text-slate-500">
            <x-platform.status-badge :status="$shell['status']" class="align-middle" />
        </p>
    </div>

    <div class="flex shrink-0 flex-wrap items-center gap-2">
        <x-ui.button variant="default" :href="route('platform.plans.edit', $plan)">
            {{ __('Edit Plan') }}
        </x-ui.button>
        <x-ui.popover side="bottom" align="end" width="52" content-class="p-1" close-on-content-click>
            <x-slot:trigger>
                <button
                    type="button"
                    class="inline-flex size-9 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-600 shadow-sm hover:bg-slate-50"
                    aria-label="{{ __('More actions') }}"
                >
                    <svg class="size-4" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <circle cx="5" cy="12" r="1.6" />
                        <circle cx="12" cy="12" r="1.6" />
                        <circle cx="19" cy="12" r="1.6" />
                    </svg>
                </button>
            </x-slot:trigger>
            <x-ui.popover.item :href="route('platform.plans.duplicate', $plan)">{{ __('Duplicate') }}</x-ui.popover.item>
            @if (! $plan->isArchived())
                <button
                    type="button"
                    class="flex w-full items-center rounded-md px-2 py-1.5 text-sm font-medium text-rose-600 hover:bg-rose-50"
                    @click="$dispatch('open-archive-plan')"
                >
                    {{ __('Archive') }}
                </button>
            @endif
        </x-ui.popover>
    </div>
</div>

<nav class="mb-6 flex gap-1 overflow-x-auto border-b border-slate-200" aria-label="{{ __('Plan sections') }}">
    @foreach ($shell['tabs'] as $tab)
        <a
            href="{{ $tab['href'] }}"
            @class([
                'shrink-0 border-b-2 px-3 py-2.5 text-sm font-medium transition-colors',
                'border-navy text-navy' => $tab['active'],
                'border-transparent text-slate-500 hover:border-slate-300 hover:text-black' => ! $tab['active'],
            ])
        >
            {{ $tab['label'] }}
        </a>
    @endforeach
</nav>

<x-auth-session-status class="mb-4 rounded-xl border border-emerald-100 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700" :status="session('status')" />

{{ $slot }}

@include('platform.plans.partials.archive-dialog', ['plan' => $plan])
