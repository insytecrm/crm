<x-tenant-layout :title="__('Templates') . ' | InSyte CRM'">
    <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-lg font-semibold text-black">{{ __('Templates') }}</h1>
            <p class="mt-1 text-sm text-slate-500">{{ __('Write reusable WhatsApp, email, and SMS messages with lead, user, and property variables.') }}</p>
        </div>
    </div>

    <x-auth-session-status class="mb-3 rounded-lg bg-emerald-50 px-4 py-2 text-sm font-medium text-emerald-700" :status="session('status')" />

    <x-tenant.list-toolbar>
        <x-slot:search>
            <form method="GET" action="{{ route('tenant.automations.templates') }}" class="w-full">
                @if ($filter !== \App\Enums\MessageTemplateFilter::All)
                    <input type="hidden" name="filter" value="{{ $filter->value }}">
                @endif
                <x-auth.icon-input
                    type="search"
                    name="search"
                    value="{{ $search }}"
                    placeholder="{{ __('Search templates...') }}"
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
                    href="{{ route('tenant.automations.templates', array_filter(['filter' => $item === \App\Enums\MessageTemplateFilter::All ? null : $item->value, 'search' => $search !== '' ? $search : null])) }}"
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
            <x-ui.button :href="route('tenant.automations.templates.create')" variant="default">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                {{ __('Create template') }}
            </x-ui.button>
        @endif
    </x-tenant.list-toolbar>

    <div class="flex flex-col gap-3">
        @forelse ($templates as $template)
            @php
                $accent = $template->channel->accent();
            @endphp
            <div class="rounded-2xl border border-slate-100 bg-white p-4 shadow-sm">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            @if ($canManage)
                                <a href="{{ route('tenant.automations.templates.edit', $template) }}" class="truncate text-sm font-semibold text-black hover:underline">
                                    {{ $template->name }}
                                </a>
                            @else
                                <p class="truncate text-sm font-semibold text-black">{{ $template->name }}</p>
                            @endif
                            <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $accent['badge'] }}">
                                {{ $template->channel->label() }}
                            </span>
                            @if ($template->isActive())
                                <span class="inline-flex rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-semibold text-emerald-700">{{ __('Active') }}</span>
                            @else
                                <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-semibold text-slate-600">{{ __('Off') }}</span>
                            @endif
                        </div>
                        @if ($template->subject)
                            <p class="mt-1 truncate text-sm text-slate-600">{{ $template->subject }}</p>
                        @endif
                        <p class="mt-1 text-sm text-slate-500">{{ $template->bodyPreview() }}</p>
                    </div>

                    @if ($canManage)
                        <div class="flex shrink-0 items-center gap-2">
                            <x-ui.button
                                variant="outline"
                                size="sm"
                                :href="route('tenant.automations.templates.edit', $template)"
                            >
                                {{ __('Edit') }}
                            </x-ui.button>

                            <form
                                method="POST"
                                action="{{ route('tenant.automations.templates.destroy', $template) }}"
                                onsubmit="return confirm(@js(__('Delete this template?')))"
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
                @if ($canManage && $filter === \App\Enums\MessageTemplateFilter::All && $search === '')
                    <div class="mt-4">
                        <x-ui.button :href="route('tenant.automations.templates.create')" variant="default">{{ __('Create template') }}</x-ui.button>
                    </div>
                @endif
            </div>
        @endforelse
    </div>

    @if ($templates->hasPages())
        <div class="mt-4">
            {{ $templates->links() }}
        </div>
    @endif
</x-tenant-layout>
