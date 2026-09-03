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
    class="flex flex-col overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-sm"
>
    <div class="flex shrink-0 items-center gap-2 border-b border-slate-100 px-3 py-2 sm:gap-3 sm:px-4">
        <div class="min-w-0 shrink-0">
            <h2 class="text-sm font-semibold leading-none text-black">{{ __('My Day') }}</h2>
            <p class="mt-0.5 text-[11px] leading-none text-slate-500">
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

        <div class="flex min-w-0 flex-1 items-center justify-center gap-1.5">
            <button
                type="button"
                @click="setKind('follow_up')"
                :aria-pressed="kind === 'follow_up'"
                :class="kind === 'follow_up'
                    ? 'border-sky-300 bg-sky-50 ring-1 ring-sky-200/80'
                    : 'border-slate-100 bg-white hover:border-slate-200 hover:bg-slate-50/80'"
                class="inline-flex h-8 max-w-full items-center gap-1.5 rounded-lg border px-2 shadow-sm shadow-slate-200/40 transition"
            >
                <span class="truncate text-[10px] font-medium text-slate-500">{{ __('Follow-ups') }}</span>
                <span class="text-sm font-bold tabular-nums leading-none text-sky-600">{{ $followUpCount }}</span>
            </button>

            <button
                type="button"
                @click="setKind('site_visit')"
                :aria-pressed="kind === 'site_visit'"
                :class="kind === 'site_visit'
                    ? 'border-amber-300 bg-amber-50 ring-1 ring-amber-200/80'
                    : 'border-slate-100 bg-white hover:border-slate-200 hover:bg-slate-50/80'"
                class="inline-flex h-8 max-w-full items-center gap-1.5 rounded-lg border px-2 shadow-sm shadow-slate-200/40 transition"
            >
                <span class="truncate text-[10px] font-medium text-slate-500">{{ __('Site Visits') }}</span>
                <span class="text-sm font-bold tabular-nums leading-none text-amber-600">{{ $siteVisitCount }}</span>
            </button>
        </div>

        <a
            href="{{ route('tenant.activities.index', ['filter' => 'today']) }}"
            class="shrink-0 text-[11px] font-semibold text-navy hover:underline"
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

@include('tenant.activities.partials.activity-modals', [
    'activities' => $activities,
])
