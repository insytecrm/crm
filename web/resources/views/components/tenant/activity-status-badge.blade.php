@props(['activity'])

@php
    [$label, $classes] = match (true) {
        ($activity['is_completed'] ?? false) => [__('Completed'), 'bg-emerald-100 text-emerald-700'],
        $activity['is_overdue'] => [__('Overdue'), 'bg-amber-100 text-amber-700'],
        $activity['occurred_at']->isToday() => [__('Due Today'), 'bg-sky-100 text-sky-700'],
        default => [__('Upcoming'), 'bg-slate-100 text-slate-700'],
    };
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold {$classes}"]) }}>
    {{ $label }}
</span>
