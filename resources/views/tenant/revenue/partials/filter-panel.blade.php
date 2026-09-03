@props([
    'filter',
    'filterOptions',
])

<div
    x-data="{ open: @js($filter->isActive()) }"
    class="mb-5 overflow-hidden rounded-2xl border border-slate-200/80 bg-gradient-to-br from-white via-slate-50/80 to-sky-50/40 shadow-sm shadow-slate-200/60"
>
    <div class="flex flex-wrap items-center justify-between gap-3 px-4 py-3 sm:px-5">
        <div class="flex items-center gap-3">
            <button
                type="button"
                @click="open = !open"
                class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-sm font-semibold text-black shadow-sm transition-all hover:border-sky-200 hover:bg-sky-50/50 hover:shadow focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-sky-500 focus-visible:ring-offset-2"
                :aria-expanded="open"
            >
                <svg class="size-4 text-sky-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 0 1-.659 1.591l-5.432 5.432a2.25 2.25 0 0 0-.659 1.591v2.927a2.25 2.25 0 0 1-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 0 0-.659-1.591L3.659 7.409A2.25 2.25 0 0 1 3 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0 1 12 3Z" />
                </svg>
                {{ __('Filters') }}
                @if ($filter->isActive())
                    <span class="inline-flex min-w-5 items-center justify-center rounded-full bg-sky-500 px-1.5 py-0.5 text-[10px] font-bold text-white">
                        {{ $filter->activeCount() }}
                    </span>
                @endif
                <svg
                    class="size-4 text-slate-400 transition-transform duration-200"
                    :class="open && 'rotate-180'"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke-width="2"
                    stroke="currentColor"
                    aria-hidden="true"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                </svg>
            </button>

            @if ($filter->isActive())
                <p class="text-xs text-slate-500">{{ __('Showing filtered results across all sections') }}</p>
            @else
                <p class="text-xs text-slate-500">{{ __('All agreed bookings') }}</p>
            @endif
        </div>

        @if ($filter->isActive())
            <x-ui.button type="button" variant="outline" :href="route('tenant.revenue.index')" class="!h-9 !px-3 !text-xs">
                {{ __('Clear filters') }}
            </x-ui.button>
        @endif
    </div>

    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 -translate-y-1"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 -translate-y-1"
        x-cloak
        class="border-t border-slate-200/80 bg-white/70 px-4 py-4 sm:px-5"
    >
        <form method="GET" action="{{ route('tenant.revenue.index') }}" class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6">
            <div>
                <label for="revenue_from" class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wide text-sky-700/80">{{ __('From') }}</label>
                <x-ui.datetime-picker
                    id="revenue_from"
                    name="from"
                    mode="date"
                    :value="$filter->dateFrom"
                    class="!border-slate-200 focus:!border-sky-400 focus:!ring-sky-400"
                />
            </div>

            <div>
                <label for="revenue_to" class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wide text-sky-700/80">{{ __('To') }}</label>
                <x-ui.datetime-picker
                    id="revenue_to"
                    name="to"
                    mode="date"
                    :value="$filter->dateTo"
                    class="!border-slate-200 focus:!border-sky-400 focus:!ring-sky-400"
                />
            </div>

            <div>
                <label for="revenue_developer" class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wide text-violet-700/80">{{ __('Developer') }}</label>
                <select
                    id="revenue_developer"
                    name="developer"
                    class="block h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm text-black shadow-sm transition-colors focus:border-violet-400 focus:ring-violet-400"
                >
                    <option value="">{{ __('All Developers') }}</option>
                    @foreach ($filterOptions['developers'] as $developer)
                        <option value="{{ $developer }}" @selected($filter->developer === $developer)>{{ $developer }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="revenue_property" class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wide text-emerald-700/80">{{ __('Project') }}</label>
                <select
                    id="revenue_property"
                    name="property"
                    class="block h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm text-black shadow-sm transition-colors focus:border-emerald-400 focus:ring-emerald-400"
                >
                    <option value="">{{ __('All Projects') }}</option>
                    @foreach ($filterOptions['properties'] as $property)
                        <option value="{{ $property['id'] }}" @selected($filter->propertyId === $property['id'])>{{ $property['name'] }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="revenue_salesperson" class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wide text-amber-700/80">{{ __('Salesperson') }}</label>
                <select
                    id="revenue_salesperson"
                    name="salesperson"
                    class="block h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm text-black shadow-sm transition-colors focus:border-amber-400 focus:ring-amber-400"
                >
                    <option value="">{{ __('All Salespeople') }}</option>
                    @foreach ($filterOptions['salespeople'] as $salesperson)
                        <option value="{{ $salesperson['id'] }}" @selected($filter->salespersonId === $salesperson['id'])>{{ $salesperson['name'] }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-end gap-2">
                <x-ui.button type="submit" variant="default" class="w-full !bg-gradient-to-r !from-sky-600 !to-emerald-600 hover:!from-sky-700 hover:!to-emerald-700">
                    {{ __('Apply Filters') }}
                </x-ui.button>
            </div>
        </form>
    </div>
</div>
