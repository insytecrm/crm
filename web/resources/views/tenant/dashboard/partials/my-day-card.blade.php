@props([
    'activities',
])

@php
    $followUpCount = $activities->where('kind', 'follow_up')->count();
    $siteVisitCount = $activities->where('kind', 'site_visit')->count();
@endphp

<section
    x-data="{
        kind: 'all',
        setKind(value) {
            this.kind = this.kind === value ? 'all' : value;
        },
        matches(activityKind) {
            return this.kind === 'all' || this.kind === activityKind;
        },
    }"
    class="flex flex-col overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-sm shadow-slate-200/40"
>
    <div class="flex shrink-0 items-center gap-2 bg-navy-dark px-3 py-2.5 sm:gap-3 sm:px-4">
        <div class="flex min-w-0 items-center gap-2.5">
            <div class="flex size-8 shrink-0 items-center justify-center rounded-lg bg-white/20 text-white">
                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                </svg>
            </div>
            <div class="min-w-0 shrink-0">
                <h2 class="text-sm font-bold leading-none text-white">{{ __('My Day') }}</h2>
                <p class="mt-0.5 text-[11px] leading-none text-white/75">
                    <span x-show="kind === 'all'">
                        {{ trans_choice(':count activity|:count activities', $activities->count(), ['count' => $activities->count()]) }}
                    </span>
                    <span x-show="kind === 'follow_up'" x-cloak>
                        {{ trans_choice(':count follow-up|:count follow-ups', $followUpCount, ['count' => $followUpCount]) }}
                    </span>
                    <span x-show="kind === 'site_visit'" x-cloak>
                        {{ trans_choice(':count site visit|:count site visits', $siteVisitCount, ['count' => $siteVisitCount]) }}
                    </span>
                </p>
            </div>
        </div>

        <div class="flex min-w-0 flex-1 items-center justify-center gap-1.5">
            <button
                type="button"
                @click="setKind('follow_up')"
                :aria-pressed="kind === 'follow_up'"
                :class="kind === 'follow_up'
                    ? 'border-white/40 bg-white/20 ring-1 ring-white/30'
                    : 'border-white/15 bg-white/5 hover:bg-white/10'"
                class="inline-flex h-8 max-w-full items-center gap-1.5 rounded-lg border px-2 transition"
            >
                <span class="truncate text-[10px] font-medium text-white/80">{{ __('Follow-ups') }}</span>
                <span class="text-sm font-bold tabular-nums leading-none text-white">{{ $followUpCount }}</span>
            </button>

            <button
                type="button"
                @click="setKind('site_visit')"
                :aria-pressed="kind === 'site_visit'"
                :class="kind === 'site_visit'
                    ? 'border-white/40 bg-white/20 ring-1 ring-white/30'
                    : 'border-white/15 bg-white/5 hover:bg-white/10'"
                class="inline-flex h-8 max-w-full items-center gap-1.5 rounded-lg border px-2 transition"
            >
                <span class="truncate text-[10px] font-medium text-white/80">{{ __('Site Visits') }}</span>
                <span class="text-sm font-bold tabular-nums leading-none text-white">{{ $siteVisitCount }}</span>
            </button>
        </div>

        <a
            href="{{ route('tenant.activities.index', ['filter' => 'today']) }}"
            class="shrink-0 text-[11px] font-semibold text-white/90 hover:text-white hover:underline"
        >
            {{ __('View all') }}
        </a>
    </div>

    {{-- Fixed viewport for ~4 activity cards; overflow scrolls instead of growing the panel --}}
    <div class="space-y-2 overflow-y-auto p-2" style="height: 29.5rem">
        @forelse ($activities as $activity)
            @php
                $lead = $activity['lead'];
                $isFollowUp = $activity['kind'] === 'follow_up';
                $timeLabel = $activity['occurred_at']->format('g:i A');
            @endphp

            <div
                x-show="matches(@js($activity['kind']))"
                x-cloak
                class="rounded-xl border border-slate-100 bg-slate-50/70 p-2.5 shadow-sm shadow-slate-200/40"
            >
                <div class="flex items-start justify-between gap-2">
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-1.5">
                            <span @class([
                                'inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-semibold',
                                'bg-sky-100 text-sky-700' => $isFollowUp,
                                'bg-amber-100 text-amber-700' => ! $isFollowUp,
                            ])>
                                {{ $activity['label'] }}
                            </span>
                            <x-tenant.activity-status-badge :activity="$activity" class="!px-2 !py-0.5 !text-[10px]" />
                        </div>

                        <div class="mt-1.5 truncate text-xs font-semibold text-black">
                            <x-tenant.lead-link :lead="$lead" />
                        </div>

                        <p class="mt-0.5 text-[11px] text-slate-500">
                            {{ $timeLabel }}
                            @if ($activity['property_label'] ?? null)
                                <span class="text-slate-300">·</span>
                                {{ $activity['property_label'] }}
                            @endif
                        </p>
                    </div>

                    <div class="shrink-0">
                        @include('tenant.activities.partials.activity-action-icons', [
                            'activity' => $activity,
                        ])
                    </div>
                </div>
            </div>
        @empty
            <div class="flex h-full items-center justify-center rounded-xl border border-dashed border-slate-200 bg-slate-50/50 px-3">
                <p class="text-center text-xs text-slate-500">{{ __('No activities for today.') }}</p>
            </div>
        @endforelse

        @if ($activities->isNotEmpty())
            <div
                x-show="kind === 'follow_up' && {{ $followUpCount }} === 0"
                x-cloak
                class="flex h-full items-center justify-center rounded-xl border border-dashed border-slate-200 bg-slate-50/50 px-3"
            >
                <p class="text-center text-xs text-slate-500">{{ __('No follow-ups for today.') }}</p>
            </div>
            <div
                x-show="kind === 'site_visit' && {{ $siteVisitCount }} === 0"
                x-cloak
                class="flex h-full items-center justify-center rounded-xl border border-dashed border-slate-200 bg-slate-50/50 px-3"
            >
                <p class="text-center text-xs text-slate-500">{{ __('No site visits for today.') }}</p>
            </div>
        @endif
    </div>
</section>

@push('modals')
    @include('tenant.activities.partials.activity-modals', [
        'activities' => $activities,
    ])
@endpush
