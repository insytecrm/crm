<x-tenant-layout :title="__('Workflows') . ' | InSyte CRM'">
    <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-lg font-semibold text-black">{{ __('Workflows') }}</h1>
            <p class="mt-1 text-sm text-slate-500">{{ __('Automate follow-ups and tasks for leads assigned to you.') }}</p>
        </div>
    </div>

    <x-auth-session-status class="mb-3 rounded-lg bg-emerald-50 px-4 py-2 text-sm font-medium text-emerald-700" :status="session('status')" />
    <x-input-error class="mb-3" :messages="$errors->get('is_active')" />

    <x-tenant.list-toolbar>
        <x-slot:search>
            <form method="GET" action="{{ route('tenant.automations.workflows') }}" class="w-full">
                @if ($filter !== \App\Enums\AutomationWorkflowFilter::All)
                    <input type="hidden" name="filter" value="{{ $filter->value }}">
                @endif
                <x-auth.icon-input
                    type="search"
                    name="search"
                    value="{{ $search }}"
                    placeholder="{{ __('Search workflows...') }}"
                >
                    <x-slot:icon>
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" /></svg>
                    </x-slot:icon>
                </x-auth.icon-input>
            </form>
        </x-slot:search>

        <div class="flex rounded-lg border border-slate-200 bg-white p-0.5 shadow-sm">
            @foreach ($filters as $item)
                <a
                    href="{{ route('tenant.automations.workflows', array_filter(['filter' => $item === \App\Enums\AutomationWorkflowFilter::All ? null : $item->value, 'search' => $search !== '' ? $search : null])) }}"
                    @class([
                        'rounded-md px-3 py-1.5 text-xs font-semibold transition-colors',
                        'bg-navy text-white' => $filter === $item,
                        'text-slate-600 hover:bg-slate-50' => $filter !== $item,
                    ])
                >
                    {{ $item->label() }}
                </a>
            @endforeach
        </div>

        @if ($canManage)
            <form method="POST" action="{{ route('tenant.automations.workflows.store') }}">
                @csrf
                <x-ui.button type="submit" variant="default">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                    {{ __('Create workflow') }}
                </x-ui.button>
            </form>
        @endif
    </x-tenant.list-toolbar>

    <div class="space-y-3">
        @forelse ($workflows as $workflow)
            <div @class([
                'rounded-2xl border bg-white p-4 shadow-sm',
                'border-slate-100' => $workflow->isActive(),
                'border-slate-100 bg-slate-50/60' => ! $workflow->isActive(),
            ])>
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            @if ($canManage)
                                <a href="{{ route('tenant.automations.workflows.edit', $workflow) }}" class="truncate text-sm font-semibold text-black hover:underline">
                                    {{ $workflow->name }}
                                </a>
                            @else
                                <p class="truncate text-sm font-semibold text-black">{{ $workflow->name }}</p>
                            @endif
                            @if ($workflow->isActive())
                                <span class="inline-flex rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-semibold text-emerald-700">{{ __('On') }}</span>
                            @else
                                <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-semibold text-slate-600">{{ __('Draft') }}</span>
                            @endif
                        </div>
                        <p class="mt-1 text-sm text-slate-500">{{ $workflow->recipeSummary() }}</p>
                        <p class="mt-1 text-xs text-slate-400">
                            @if ($workflow->last_run_at)
                                {{ __('Last run :date', ['date' => $workflow->last_run_at->format('M j, Y g:i A')]) }}
                            @else
                                {{ __('Never run') }}
                            @endif
                        </p>
                    </div>

                    @if ($canManage)
                        <div class="flex shrink-0 items-center gap-2">
                            <form
                                method="POST"
                                action="{{ route('tenant.automations.workflows.status.update', $workflow) }}"
                                class="inline-flex"
                                @change="$el.submit()"
                            >
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="is_active" value="0">
                                <label
                                    title="{{ __('On') }}"
                                    aria-label="{{ __('Toggle workflow') }}"
                                    class="inline-flex cursor-pointer items-center"
                                >
                                    <span class="relative inline-flex h-6 w-11 shrink-0">
                                        <input
                                            type="checkbox"
                                            name="is_active"
                                            value="1"
                                            class="peer sr-only"
                                            @checked($workflow->isActive())
                                        >
                                        <span class="absolute inset-0 rounded-full bg-slate-200 transition-colors peer-checked:bg-navy peer-focus-visible:outline-none peer-focus-visible:ring-2 peer-focus-visible:ring-navy/40"></span>
                                        <span class="absolute left-0.5 top-0.5 size-5 rounded-full bg-white shadow-sm transition-transform peer-checked:translate-x-5"></span>
                                    </span>
                                </label>
                            </form>

                            <x-ui.button
                                variant="outline"
                                size="sm"
                                :href="route('tenant.automations.workflows.edit', $workflow)"
                            >
                                {{ __('Edit') }}
                            </x-ui.button>

                            <form
                                method="POST"
                                action="{{ route('tenant.automations.workflows.destroy', $workflow) }}"
                                onsubmit="return confirm(@js(__('Delete this workflow?')))"
                            >
                                @csrf
                                @method('DELETE')
                                <x-ui.action-icon icon="delete" type="submit" :title="__('Delete')" />
                            </form>
                        </div>
                    @endif
                </div>
            </div>
        @empty
            <div class="rounded-2xl border border-slate-100 bg-white px-4 py-12 text-center shadow-sm">
                <p class="text-sm text-slate-500">{{ $filter->emptyMessage() }}</p>
                @if ($canManage && $filter === \App\Enums\AutomationWorkflowFilter::All && $search === '')
                    <form method="POST" action="{{ route('tenant.automations.workflows.store') }}" class="mt-4">
                        @csrf
                        <x-ui.button type="submit" variant="default">{{ __('Create workflow') }}</x-ui.button>
                    </form>
                @endif
            </div>
        @endforelse
    </div>

    @if ($workflows->hasPages())
        <div class="mt-4">
            {{ $workflows->links() }}
        </div>
    @endif
</x-tenant-layout>
