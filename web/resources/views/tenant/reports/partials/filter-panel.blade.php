@props([
    'periodFilter',
    'action' => null,
    'clearHref' => null,
    'showScopeFilters' => false,
    'teams' => null,
    'users' => null,
])

@php
    use App\Support\DashboardPeriodFilter;

    $formAction = $action ?? route('tenant.reports.index');
    $clearUrl = $clearHref ?? route('tenant.reports.index');
    $teams = $teams ?? collect();
    $users = $users ?? collect();
    $isFiltered = $showScopeFilters
        ? $periodFilter->isAnalyticsFiltered()
        : $periodFilter->isReportsFiltered();
@endphp

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
    <form method="GET" action="{{ $formAction }}" class="flex flex-col gap-4 sm:flex-row sm:flex-wrap sm:items-end">
        <div class="min-w-0 flex-1 sm:max-w-xs">
            <label for="reports_period" class="mb-1.5 block text-xs font-semibold text-slate-600">{{ __('Time period') }}</label>
            <select
                id="reports_period"
                name="period"
                class="block h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm text-black shadow-sm transition-colors focus:border-navy focus:ring-navy"
            >
                @foreach (DashboardPeriodFilter::reportsSelectablePeriods() as $option)
                    <option value="{{ $option->value }}" @selected($periodFilter->period === $option)>
                        {{ $option->label() }}
                    </option>
                @endforeach
            </select>
        </div>

        @if ($showScopeFilters)
            <div class="min-w-0 flex-1 sm:max-w-xs">
                <label for="reports_team" class="mb-1.5 block text-xs font-semibold text-slate-600">{{ __('Team') }}</label>
                <select
                    id="reports_team"
                    name="team"
                    class="block h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm text-black shadow-sm transition-colors focus:border-navy focus:ring-navy"
                >
                    <option value="">{{ __('All Teams') }}</option>
                    @foreach ($teams as $team)
                        <option value="{{ $team->id }}" @selected($periodFilter->teamId === $team->id)>
                            {{ $team->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="min-w-0 flex-1 sm:max-w-xs">
                <label for="reports_user" class="mb-1.5 block text-xs font-semibold text-slate-600">{{ __('User') }}</label>
                <select
                    id="reports_user"
                    name="user"
                    class="block h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm text-black shadow-sm transition-colors focus:border-navy focus:ring-navy"
                >
                    <option value="">{{ __('All Users') }}</option>
                    @foreach ($users as $user)
                        <option value="{{ $user->id }}" @selected($periodFilter->userId === $user->id)>
                            {{ $user->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        @endif

        <div class="flex items-center gap-2">
            <x-ui.button type="submit" variant="primary">{{ __('Apply') }}</x-ui.button>
            @if ($isFiltered)
                <x-ui.button type="button" variant="soft" :href="$clearUrl">
                    {{ __('Clear') }}
                </x-ui.button>
            @endif
        </div>
    </form>
</div>
