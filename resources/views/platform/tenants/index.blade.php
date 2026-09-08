@php
    $statusFilters = [
        null => ['label' => __('Total'), 'count' => $statistics['total'], 'accent' => 'navy'],
        'active' => ['label' => __('Active'), 'count' => $statistics['active'], 'accent' => 'emerald'],
        'trial' => ['label' => __('Trial'), 'count' => $statistics['trial'], 'accent' => 'amber'],
        'past_due' => ['label' => __('Overdue'), 'count' => $statistics['past_due'], 'accent' => 'rose'],
        'suspended' => ['label' => __('Suspended'), 'count' => $statistics['suspended'], 'accent' => 'rose'],
    ];
@endphp

<x-app-layout :title="__('Channel Partners') . ' | InSyte CRM'">
    <x-platform.page-header
        :title="__('Channel Partners')"
        :description="__('Manage all businesses using InSyte.')"
    >
        <x-slot:actions>
            <x-ui.button
                type="button"
                variant="default"
                x-on:click="$dispatch('open-modal', 'create-quotation')"
            >
                {{ __('Create Quotation') }}
            </x-ui.button>
        </x-slot:actions>
    </x-platform.page-header>

    <x-auth-session-status class="mb-4 rounded-xl border border-emerald-100 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700" :status="session('status')" />
    <x-input-error class="mb-4" :messages="$errors->get('tenant')" />

    <div class="mb-4 grid grid-cols-2 gap-2 sm:grid-cols-5">
        @foreach ($statusFilters as $statusKey => $stat)
            <a
                href="{{ route('tenants.index', array_filter(['search' => $filters['search'] ?: null, 'status' => $statusKey])) }}"
                @class([
                    'rounded-xl border bg-white px-3 py-3 shadow-sm shadow-slate-200/40 transition-colors',
                    'border-navy ring-1 ring-navy/10' => ($filters['status'] ?? null) === $statusKey,
                    'border-slate-100 hover:border-slate-200 hover:bg-slate-50/50' => ($filters['status'] ?? null) !== $statusKey,
                ])
            >
                <p class="text-xs font-medium text-slate-500">{{ $stat['label'] }}</p>
                <p @class([
                    'mt-1 text-xl font-bold tracking-tight',
                    'text-emerald-600' => $stat['accent'] === 'emerald',
                    'text-amber-600' => $stat['accent'] === 'amber',
                    'text-rose-600' => $stat['accent'] === 'rose',
                    'text-black' => $stat['accent'] === 'navy',
                ])>{{ number_format($stat['count']) }}</p>
            </a>
        @endforeach
    </div>

    <x-platform.panel compact>
        <form method="GET" action="{{ route('tenants.index') }}" class="mb-4 flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            @if ($filters['status'])
                <input type="hidden" name="status" value="{{ $filters['status'] }}">
            @endif

            <div class="w-full lg:max-w-md">
                <x-auth.icon-input
                    type="search"
                    name="search"
                    value="{{ $filters['search'] }}"
                    placeholder="{{ __('Search company, owner, email...') }}"
                    onchange="this.form.submit()"
                >
                    <x-slot:icon>
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35m0 0a7 7 0 1 0-9.9-9.9 7 7 0 0 0 9.9 9.9Z" />
                        </svg>
                    </x-slot:icon>
                </x-auth.icon-input>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <x-ui.button
                    type="button"
                    variant="outline"
                    size="sm"
                    class="pointer-events-none opacity-70"
                >
                    {{ __('Status') }}
                </x-ui.button>
                <x-ui.button
                    type="button"
                    variant="outline"
                    size="sm"
                    class="pointer-events-none opacity-70"
                >
                    {{ __('Plan') }}
                </x-ui.button>
                <x-ui.button
                    type="button"
                    variant="outline"
                    size="sm"
                    class="pointer-events-none opacity-70"
                >
                    {{ __('Billing') }}
                </x-ui.button>
                <x-ui.button
                    type="button"
                    variant="outline"
                    size="sm"
                    class="pointer-events-none opacity-70"
                >
                    {{ __('More Filters') }}
                </x-ui.button>
            </div>
        </form>

        @if ($tenants->isEmpty())
            <div class="rounded-xl border border-dashed border-slate-200 bg-slate-50/70 px-4 py-10 text-center">
                <p class="text-sm font-medium text-black">{{ __('No channel partners found.') }}</p>
                <p class="mt-1 text-sm text-slate-500">{{ __('Create a quotation to start onboarding, or adjust your search filters.') }}</p>
                <div class="mt-4">
                    <x-ui.button
                        type="button"
                        variant="default"
                        x-on:click="$dispatch('open-modal', 'create-quotation')"
                    >
                        {{ __('Create Quotation') }}
                    </x-ui.button>
                </div>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100">
                    <thead>
                        <tr>
                            <th class="px-3 py-3 text-start text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Channel Partner') }}</th>
                            <th class="px-3 py-3 text-start text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Plan') }}</th>
                            <th class="px-3 py-3 text-start text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Users') }}</th>
                            <th class="px-3 py-3 text-start text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Status') }}</th>
                            <th class="px-3 py-3 text-start text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Last Active') }}</th>
                            <th class="px-3 py-3 text-end text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($tenants as $tenant)
                            <tr
                                class="cursor-pointer hover:bg-slate-50/80"
                                onclick="window.location='{{ route('tenants.show', $tenant) }}'"
                            >
                                <td class="px-3 py-4 whitespace-nowrap">
                                    <div class="font-medium text-black">{{ $tenant->name }}</div>
                                    <div class="mt-0.5 text-xs text-slate-500">{{ $tenant->email ?: $tenant->id }}</div>
                                </td>
                                <td class="px-3 py-4 whitespace-nowrap text-sm text-slate-600">{{ $tenant->plan_label }}</td>
                                <td class="px-3 py-4 whitespace-nowrap text-sm font-medium tabular-nums text-black">
                                    {{ number_format($tenant->users_count) }}{{ $tenant->users_limit ? '/'.number_format($tenant->users_limit) : '' }}
                                </td>
                                <td class="px-3 py-4 whitespace-nowrap">
                                    <x-platform.status-badge :status="$tenant->status" />
                                </td>
                                <td class="px-3 py-4 whitespace-nowrap text-sm text-slate-600">{{ $tenant->last_active_label }}</td>
                                <td class="px-3 py-4 whitespace-nowrap text-end" onclick="event.stopPropagation()">
                                    <x-ui.action-icon-group>
                                        <x-ui.action-icon
                                            icon="view"
                                            :href="route('tenants.show', $tenant)"
                                            :title="__('Overview')"
                                        />
                                        <x-ui.action-icon
                                            icon="edit"
                                            type="button"
                                            :title="__('Edit')"
                                            data-partner-id="{{ $tenant->id }}"
                                            x-on:click.stop="$dispatch('open-edit-partner', $el.dataset.partnerId)"
                                        />
                                    </x-ui.action-icon-group>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $tenants->links() }}
            </div>
        @endif
    </x-platform.panel>

    @include('platform.tenants.partials.edit-drawers')
    @include('platform.quotations.partials.create-wizard-modal')
    @include('platform.tenants.partials.add-wizard-modal')
</x-app-layout>
