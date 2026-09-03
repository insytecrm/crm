@php
    $history = app(\App\Support\LeadHistorySummary::class)->for($lead);
    $journey = $history['journey'];
    $lastIndex = max(count($journey) - 1, 1);
    $progressIndex = 0;

    foreach ($journey as $index => $step) {
        if ($step['state'] === 'completed') {
            $progressIndex = $index;
        }

        if ($step['state'] === 'current') {
            $progressIndex = $index;
            break;
        }
    }

    $allComplete = collect($journey)->every(fn (array $step): bool => $step['state'] === 'completed');
    $progressPercent = $allComplete ? 100 : (int) round(($progressIndex / $lastIndex) * 100);
@endphp

<div class="space-y-5">
    <div>
        <h3 class="mb-3 text-sm font-semibold uppercase tracking-wider text-slate-500">{{ __('Lead journey') }}</h3>

        <ol class="relative flex" aria-label="{{ __('Lead journey') }}">
            <span class="pointer-events-none absolute inset-x-4 top-3 h-2 overflow-hidden rounded-full bg-slate-200" aria-hidden="true">
                <span class="block h-full rounded-full bg-navy" style="width: {{ $progressPercent }}%"></span>
                <span class="absolute inset-x-1 top-1/2 h-px -translate-y-1/2 bg-[repeating-linear-gradient(90deg,rgba(255,255,255,0.9)_0_6px,transparent_6px_12px)]"></span>
            </span>

            @foreach ($journey as $step)
                @php
                    $shortLabel = match ($step['key']) {
                        'created' => __('Created'),
                        'site_visit' => __('Visit'),
                        'invoice' => __('Invoice'),
                        'payout' => __('Payout'),
                        default => $step['label'],
                    };
                    $tooltipParts = array_filter([
                        $step['label'],
                        $step['detail'],
                        $step['occurred_at']?->format('M j, Y g:i A'),
                        $step['state'] === 'current' ? __('In progress') : null,
                        $step['state'] === 'skipped' ? __('Skipped') : null,
                    ]);
                @endphp
                <li class="relative z-10 flex min-w-0 flex-1 flex-col items-center text-center">
                    <span
                        @class([
                            'flex size-6 shrink-0 items-center justify-center rounded-full border-2 bg-white shadow-sm',
                            'border-navy text-navy' => $step['state'] === 'completed',
                            'border-navy text-navy ring-4 ring-navy/15' => $step['state'] === 'current',
                            'border-slate-200 text-slate-300' => $step['state'] === 'upcoming',
                            'border-dashed border-slate-200 text-slate-300' => $step['state'] === 'skipped',
                        ])
                        title="{{ implode(' · ', $tooltipParts) }}"
                    >
                        @if ($step['state'] === 'completed')
                            <svg class="size-3" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                        @elseif ($step['state'] === 'current')
                            <span class="size-2 rounded-full bg-navy" aria-hidden="true"></span>
                        @endif
                    </span>
                    <p
                        @class([
                            'mt-2 w-full truncate px-0.5 text-[10px] font-semibold leading-tight',
                            'text-black' => in_array($step['state'], ['completed', 'current'], true),
                            'text-slate-400' => in_array($step['state'], ['upcoming', 'skipped'], true),
                        ])
                    >{{ $shortLabel }}</p>
                    <p class="mt-0.5 text-[10px] leading-tight text-slate-400">
                        @if ($step['occurred_at'])
                            {{ $step['occurred_at']->format('M j') }}
                        @elseif ($step['state'] === 'current')
                            {{ __('In progress') }}
                        @elseif ($step['state'] === 'skipped')
                            {{ __('Skipped') }}
                        @endif
                    </p>
                </li>
            @endforeach
        </ol>
    </div>

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
