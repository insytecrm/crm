@props([
    'filter',
    'filterOptions',
])

<div
    x-show="filtersOpen"
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0 -translate-y-1"
    x-transition:enter-end="opacity-100 translate-y-0"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100 translate-y-0"
    x-transition:leave-end="opacity-0 -translate-y-1"
    x-cloak
    class="border-t border-slate-100 px-3 py-4 sm:px-4"
>
    <form method="GET" action="{{ route('tenant.revenue.index') }}" class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6">
        <div>
            <label for="revenue_from" class="mb-1.5 block text-xs font-semibold text-slate-600">{{ __('From') }}</label>
            <x-ui.datetime-picker
                id="revenue_from"
                name="from"
                mode="date"
                :value="$filter->dateFrom"
            />
        </div>

        <div>
            <label for="revenue_to" class="mb-1.5 block text-xs font-semibold text-slate-600">{{ __('To') }}</label>
            <x-ui.datetime-picker
                id="revenue_to"
                name="to"
                mode="date"
                :value="$filter->dateTo"
            />
        </div>

        <div>
            <label for="revenue_developer" class="mb-1.5 block text-xs font-semibold text-slate-600">{{ __('Developer') }}</label>
            <select
                id="revenue_developer"
                name="developer"
                class="block h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm text-black shadow-sm transition-colors focus:border-navy focus:ring-navy"
            >
                <option value="">{{ __('All Developers') }}</option>
                @foreach ($filterOptions['developers'] as $developer)
                    <option value="{{ $developer }}" @selected($filter->developer === $developer)>{{ $developer }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="revenue_property" class="mb-1.5 block text-xs font-semibold text-slate-600">{{ __('Project') }}</label>
            <select
                id="revenue_property"
                name="property"
                class="block h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm text-black shadow-sm transition-colors focus:border-navy focus:ring-navy"
            >
                <option value="">{{ __('All Projects') }}</option>
                @foreach ($filterOptions['properties'] as $property)
                    <option value="{{ $property['id'] }}" @selected($filter->propertyId === $property['id'])>{{ $property['name'] }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="revenue_salesperson" class="mb-1.5 block text-xs font-semibold text-slate-600">{{ __('Salesperson') }}</label>
            <select
                id="revenue_salesperson"
                name="salesperson"
                class="block h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm text-black shadow-sm transition-colors focus:border-navy focus:ring-navy"
            >
                <option value="">{{ __('All Salespeople') }}</option>
                @foreach ($filterOptions['salespeople'] as $salesperson)
                    <option value="{{ $salesperson['id'] }}" @selected($filter->salespersonId === $salesperson['id'])>{{ $salesperson['name'] }}</option>
                @endforeach
            </select>
        </div>

        <div class="flex items-end gap-2">
            <x-ui.button type="submit" variant="default" class="w-full">
                {{ __('Apply Filters') }}
            </x-ui.button>
        </div>
    </form>
</div>
