@php
    $filterOptions = [
        'all' => __('All'),
        'follow_up' => __('Follow-ups'),
        'site_visit' => __('Site visits'),
        'task' => __('Tasks'),
        'overdue' => __('Overdue'),
    ];
@endphp

<x-tenant-layout :title="__('Work') . ' | InSyte CRM'">
    <div class="mb-5 flex flex-wrap items-end justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-black">{{ __('Work') }}</h1>
            <p class="mt-1 text-sm text-slate-500">{{ __('Your queue for today — follow-ups, visits, and tasks in one place.') }}</p>
        </div>
        @unless (auth()->user()->isAgentRole())
            <x-ui.button type="button" variant="outline" size="sm" :href="route('tenant.dashboard')">
                {{ __('Dashboard') }}
            </x-ui.button>
        @endunless
    </div>

    <div class="mb-4 flex flex-wrap gap-2">
        @foreach ($filterOptions as $value => $label)
            <a
                href="{{ route('tenant.work.index', $value === 'all' ? [] : ['filter' => $value]) }}"
                @class([
                    'inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold transition',
                    'bg-navy text-white' => $filter === $value,
                    'bg-slate-100 text-slate-600 hover:bg-slate-200' => $filter !== $value,
                ])
            >
                {{ $label }}
            </a>
        @endforeach
    </div>

    <div class="space-y-2">
        @forelse ($items as $item)
            @php
                $lead = $item['lead'];
                $isFollowUp = $item['kind'] === 'follow_up';
            @endphp
            <div class="rounded-xl border border-slate-100 bg-white p-3 shadow-sm shadow-slate-200/40">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-1.5">
                            <span @class([
                                'inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-semibold',
                                'bg-sky-100 text-sky-700' => $isFollowUp,
                                'bg-amber-100 text-amber-700' => $item['kind'] === 'site_visit',
                                'bg-violet-100 text-violet-700' => $item['kind'] === 'task',
                            ])>
                                {{ $item['label'] }}
                            </span>
                            @if ($item['is_overdue'])
                                <span class="inline-flex rounded-full bg-rose-100 px-2 py-0.5 text-[10px] font-semibold text-rose-700">{{ __('Overdue') }}</span>
                            @endif
                        </div>
                        <button
                            type="button"
                            class="mt-1.5 truncate text-left text-sm font-semibold text-black hover:underline"
                            @click="$dispatch('open-lead', { id: {{ $lead->id }}, intent: @js($item['intent']) })"
                        >
                            {{ $lead->name }}
                        </button>
                        <p class="mt-0.5 text-[11px] text-slate-500">
                            {{ $item['occurred_at']->format('M j, g:i A') }}
                            @if ($item['property_label'])
                                <span class="text-slate-300">·</span> {{ $item['property_label'] }}
                            @endif
                        </p>
                    </div>
                    <x-ui.button
                        type="button"
                        variant="default"
                        size="sm"
                        @click="$dispatch('open-lead', { id: {{ $lead->id }}, intent: @js($item['intent']) })"
                    >
                        {{ __('Open') }}
                    </x-ui.button>
                </div>
            </div>
        @empty
            <div class="rounded-xl border border-dashed border-slate-200 bg-slate-50/50 px-4 py-10 text-center">
                <p class="text-sm text-slate-500">{{ __('Nothing due in this queue right now.') }}</p>
            </div>
        @endforelse
    </div>

    @if ($managerTriage['unassigned']->isNotEmpty() || $managerTriage['stale']->isNotEmpty())
        <div class="mt-8 grid gap-4 lg:grid-cols-2">
            @if ($managerTriage['unassigned']->isNotEmpty())
                <section class="rounded-2xl border border-slate-100 bg-white p-4 shadow-sm">
                    <h2 class="text-sm font-bold text-black">{{ __('Unassigned leads') }}</h2>
                    <ul class="mt-3 space-y-2">
                        @foreach ($managerTriage['unassigned'] as $lead)
                            <li class="flex items-center justify-between gap-2 text-sm">
                                <button type="button" class="font-medium text-navy hover:underline" @click="$dispatch('open-lead', {{ $lead->id }})">{{ $lead->name }}</button>
                                <a href="{{ route('tenant.leads.unassigned.index') }}" class="text-xs text-slate-500 hover:underline">{{ __('Triage') }}</a>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif
            @if ($managerTriage['stale']->isNotEmpty())
                <section class="rounded-2xl border border-slate-100 bg-white p-4 shadow-sm">
                    <h2 class="text-sm font-bold text-black">{{ __('Stale leads') }}</h2>
                    <ul class="mt-3 space-y-2">
                        @foreach ($managerTriage['stale'] as $lead)
                            <li class="flex items-center justify-between gap-2 text-sm">
                                <button type="button" class="font-medium text-navy hover:underline" @click="$dispatch('open-lead', {{ $lead->id }})">{{ $lead->name }}</button>
                                <span class="text-xs text-slate-500">{{ $lead->last_activity_at?->diffForHumans() ?? __('No activity') }}</span>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif
        </div>
    @endif
</x-tenant-layout>
