@props(['task'])

@php
    $classes = match ($task->status) {
        \App\Enums\TaskStatus::Pending => 'bg-slate-100 text-slate-700',
        \App\Enums\TaskStatus::InProgress => 'bg-sky-100 text-sky-700',
        \App\Enums\TaskStatus::Complete => 'bg-emerald-100 text-emerald-700',
        \App\Enums\TaskStatus::Cancelled => 'bg-rose-100 text-rose-700',
    };
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold {$classes}"]) }}>
    {{ $task->statusLabel() }}
</span>
