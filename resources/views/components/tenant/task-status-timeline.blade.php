@props(['task'])

@php
    $steps = \App\Enums\TaskStatus::timeline();
    $currentIndex = array_search($task->status, $steps, true);
@endphp

<div {{ $attributes->merge(['class' => 'flex flex-wrap items-center gap-2']) }}>
    @foreach ($steps as $index => $step)
        @php
            $isCurrent = $task->status === $step;
            $isPast = $currentIndex !== false && $index < $currentIndex && $task->status !== \App\Enums\TaskStatus::Cancelled;
            $isCancelledCurrent = $task->status === \App\Enums\TaskStatus::Cancelled && $step === \App\Enums\TaskStatus::Cancelled;

            $stepClasses = match (true) {
                $isCancelledCurrent => 'border-rose-300 bg-rose-50 text-rose-700 ring-1 ring-rose-200',
                $isCurrent && $step === \App\Enums\TaskStatus::Complete => 'border-emerald-300 bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200',
                $isCurrent && $step === \App\Enums\TaskStatus::InProgress => 'border-sky-300 bg-sky-50 text-sky-700 ring-1 ring-sky-200',
                $isCurrent => 'border-navy/20 bg-navy/5 text-black ring-1 ring-navy/10',
                $isPast => 'border-emerald-200 bg-emerald-50/70 text-emerald-700',
                $task->status === \App\Enums\TaskStatus::Cancelled => 'border-slate-200 bg-slate-50 text-slate-400',
                default => 'border-slate-200 bg-white text-slate-500',
            };
        @endphp

        <div class="flex items-center gap-2">
            <div @class([
                'rounded-full border px-3 py-1 text-xs font-semibold transition-colors',
                $stepClasses,
            ])>
                {{ $step->label() }}
            </div>

            @if (! $loop->last)
                <span @class([
                    'hidden text-slate-300 sm:inline',
                    $isPast ? 'text-emerald-300' : '',
                ]) aria-hidden="true">→</span>
            @endif
        </div>
    @endforeach
</div>
