@props([
    'listing',
    'listFilters',
    'search',
    'users',
    'sources',
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
    <form
        method="GET"
        action="{{ route($listing->routeName(), $listing->routeName() === 'tenant.leads.index' ? $listing->redirectParameters($search) : []) }}"
        class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4"
    >
        @if ($listing !== \App\Enums\LeadListingFilter::All)
            <input type="hidden" name="filter" value="{{ $listing->value }}">
        @endif

        @if ($search !== '')
            <input type="hidden" name="search" value="{{ $search }}">
        @endif

        <div>
            <label for="lead_filter_assigned_to" class="mb-1.5 block text-xs font-semibold text-slate-600">{{ __('Assigned To') }}</label>
            <select
                id="lead_filter_assigned_to"
                name="assigned_to"
                class="block h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm text-black shadow-sm transition-colors focus:border-navy focus:ring-navy"
            >
                <option value="">{{ __('All') }}</option>
                <option value="unassigned" @selected($listFilters->assignedTo === 'unassigned')>{{ __('Unassigned') }}</option>
                @foreach ($users as $user)
                    <option value="{{ $user->id }}" @selected($listFilters->assignedTo === (string) $user->id)>{{ $user->name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="lead_filter_status" class="mb-1.5 block text-xs font-semibold text-slate-600">{{ __('Status') }}</label>
            <select
                id="lead_filter_status"
                name="status"
                class="block h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm text-black shadow-sm transition-colors focus:border-navy focus:ring-navy"
            >
                <option value="">{{ __('All') }}</option>
                @foreach (\App\Enums\LeadStatus::cases() as $status)
                    <option value="{{ $status->value }}" @selected($listFilters->status === $status->value)>{{ $status->label() }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="lead_filter_source" class="mb-1.5 block text-xs font-semibold text-slate-600">{{ __('Source') }}</label>
            <select
                id="lead_filter_source"
                name="source"
                class="block h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm text-black shadow-sm transition-colors focus:border-navy focus:ring-navy"
            >
                <option value="">{{ __('All') }}</option>
                @foreach ($sources as $source)
                    <option value="{{ $source->value }}" @selected($listFilters->source === $source->value)>{{ $source->label() }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="lead_filter_budget" class="mb-1.5 block text-xs font-semibold text-slate-600">{{ __('Budget') }}</label>
            <select
                id="lead_filter_budget"
                name="budget"
                class="block h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm text-black shadow-sm transition-colors focus:border-navy focus:ring-navy"
            >
                <option value="">{{ __('All') }}</option>
                @foreach (\App\Enums\LeadBudget::cases() as $budget)
                    <option value="{{ $budget->value }}" @selected($listFilters->budget === $budget->value)>{{ $budget->label() }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="lead_filter_property_type" class="mb-1.5 block text-xs font-semibold text-slate-600">{{ __('Property Type') }}</label>
            <select
                id="lead_filter_property_type"
                name="property_type"
                class="block h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm text-black shadow-sm transition-colors focus:border-navy focus:ring-navy"
            >
                <option value="">{{ __('All') }}</option>
                @foreach (\App\Enums\PropertyType::cases() as $propertyType)
                    <option value="{{ $propertyType->value }}" @selected($listFilters->propertyType === $propertyType->value)>{{ $propertyType->label() }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="lead_filter_location" class="mb-1.5 block text-xs font-semibold text-slate-600">{{ __('Location') }}</label>
            <input
                id="lead_filter_location"
                type="text"
                name="location"
                value="{{ $listFilters->location }}"
                placeholder="{{ __('Search location...') }}"
                class="block h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm text-black shadow-sm transition-colors focus:border-navy focus:ring-navy"
            >
        </div>

        <div>
            <label for="lead_filter_created_from" class="mb-1.5 block text-xs font-semibold text-slate-600">{{ __('Created From') }}</label>
            <x-ui.datetime-picker
                id="lead_filter_created_from"
                name="created_from"
                mode="date"
                :value="$listFilters->createdFrom"
                class="!border-slate-200 focus:!border-navy focus:!ring-navy"
            />
        </div>

        <div>
            <label for="lead_filter_created_to" class="mb-1.5 block text-xs font-semibold text-slate-600">{{ __('Created To') }}</label>
            <x-ui.datetime-picker
                id="lead_filter_created_to"
                name="created_to"
                mode="date"
                :value="$listFilters->createdTo"
                class="!border-slate-200 focus:!border-navy focus:!ring-navy"
            />
        </div>

        <div class="flex items-end gap-2 sm:col-span-2 lg:col-span-1">
            <x-ui.button type="submit" variant="default" class="w-full">
                {{ __('Apply Filters') }}
            </x-ui.button>
            @if ($listFilters->isActive())
                <x-ui.button
                    type="button"
                    variant="outline"
                    :href="route($listing->routeName(), $listing->routeName() === 'tenant.leads.index' ? $listing->redirectParameters($search) : [])"
                    class="!h-10"
                >
                    {{ __('Clear') }}
                </x-ui.button>
            @endif
        </div>
    </form>
</div>
