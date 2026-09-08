<x-tenant-layout :title="__('Performance') . ' | ' . __('Teams') . ' | InSyte CRM'">
    <div x-data="{ filtersOpen: @js($periodFilter->isReportsFiltered()) }">
        <x-tenant.list-toolbar>
            <x-ui.button
                type="button"
                variant="soft"
                @click="filtersOpen = !filtersOpen"
                x-bind:class="filtersOpen && 'ring-2 ring-navy/20'"
                x-bind:aria-expanded="filtersOpen"
            >
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 0 1-.659 1.591l-5.432 5.432a2.25 2.25 0 0 0-.659 1.591v2.927a2.25 2.25 0 0 1-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 0 0-.659-1.591L3.659 7.409A2.25 2.25 0 0 1 3 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0 1 12 3Z" />
                </svg>
                {{ __('Filter') }}
                @if ($periodFilter->isReportsFiltered())
                    <span class="inline-flex min-w-5 items-center justify-center rounded-full bg-navy px-1.5 py-0.5 text-[10px] font-bold text-white">
                        1
                    </span>
                @endif
                <svg
                    class="h-4 w-4 transition-transform duration-200"
                    :class="filtersOpen && 'rotate-180'"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke-width="2"
                    stroke="currentColor"
                    aria-hidden="true"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                </svg>
            </x-ui.button>

            <x-slot:panel>
                @include('tenant.reports.partials.filter-panel', [
                    'periodFilter' => $periodFilter,
                    'action' => route('tenant.teams.performance.index'),
                    'clearHref' => route('tenant.teams.performance.index'),
                ])
            </x-slot:panel>
        </x-tenant.list-toolbar>

        @if ($cards === [])
            <div class="mt-3 rounded-xl border border-slate-100 bg-white px-4 py-12 text-center shadow-sm">
                <p class="text-sm text-slate-500">{{ __('No teams yet.') }}</p>
            </div>
        @else
            <div class="mt-3 grid gap-3 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4">
                @foreach ($cards as $card)
                    @include('tenant.teams.performance.partials.performance-card', ['card' => $card])
                @endforeach
            </div>
        @endif
    </div>
</x-tenant-layout>
