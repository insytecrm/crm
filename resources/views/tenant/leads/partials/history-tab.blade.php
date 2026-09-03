@php
    $history = app(\App\Support\LeadHistorySummary::class)->for($lead);
@endphp

<div class="space-y-5">
    <div>
        <h3 class="mb-3 text-sm font-semibold uppercase tracking-wider text-slate-500">{{ __('AI Insight') }}</h3>
        <p class="text-sm leading-relaxed text-slate-700">{{ $history['insight'] }}</p>
    </div>

    <div class="grid grid-cols-2 gap-3">
        <div class="rounded-lg border border-slate-100 bg-slate-50/80 px-3 py-2.5">
            <p class="text-xs font-medium text-slate-500">{{ __('Follow-ups completed') }}</p>
            <p class="mt-1 text-lg font-semibold text-black">{{ number_format($history['follow_ups_completed']) }}</p>
        </div>
        <div class="rounded-lg border border-slate-100 bg-slate-50/80 px-3 py-2.5">
            <p class="text-xs font-medium text-slate-500">{{ __('Site visits completed') }}</p>
            <p class="mt-1 text-lg font-semibold text-black">{{ number_format($history['site_visits_completed']) }}</p>
        </div>
    </div>

    <div>
        <h3 class="mb-3 text-sm font-semibold uppercase tracking-wider text-slate-500">{{ __('Projects visited') }}</h3>
        @if ($history['projects'] === [])
            <p class="text-sm text-slate-500">{{ __('No completed site visits yet.') }}</p>
        @else
            <div class="space-y-3">
                @foreach ($history['projects'] as $project)
                    <div class="rounded-lg border border-slate-100 p-3">
                        <p class="text-sm font-medium text-black">{{ $project['label'] }}</p>
                        <div class="mt-2 flex flex-wrap gap-x-4 gap-y-1 text-xs text-slate-500">
                            <span>{{ __('Fresh Visit') }}: {{ number_format($project['fresh_visits']) }}</span>
                            <span>{{ __('Revisit') }}: {{ number_format($project['revisits']) }}</span>
                            <span>{{ __('Total') }}: {{ number_format($project['total_visits']) }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
