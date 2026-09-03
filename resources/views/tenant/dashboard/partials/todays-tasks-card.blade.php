@props([
    'tasks',
])

@php
    use App\Enums\TaskFilter;

    $todayFilter = TaskFilter::Today;
@endphp

{{-- Height aligns bottom with My Day: My Day (header ~3.25rem + 29.5rem list) - pipeline 20rem - gap 0.5rem --}}
<section
    class="flex flex-col overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-sm"
    style="height: calc(29.5rem + 3.25rem - 20rem - 0.5rem)"
>
    <div class="flex shrink-0 items-center justify-between gap-3 border-b border-slate-100 px-3 py-2 sm:px-4">
        <div class="min-w-0">
            <h2 class="text-sm font-semibold leading-none text-black">{{ __('Today\'s Tasks') }}</h2>
            <p class="mt-0.5 text-[11px] leading-none text-slate-500">
                {{ trans_choice(':count task|:count tasks', $tasks->count(), ['count' => $tasks->count()]) }}
            </p>
        </div>
        <a
            href="{{ route('tenant.tasks.index', ['filter' => $todayFilter->value]) }}"
            class="text-[11px] font-semibold text-navy hover:underline"
        >
            {{ __('View all') }}
        </a>
    </div>

    <div class="min-h-0 flex-1 space-y-2 overflow-y-auto p-2">
        @forelse ($tasks as $task)
            @php
                $dueAt = $task->due_at ?? $task->created_at;
                $timeLabel = $dueAt?->format('g:i A');
                [$dueLabel, $dueClasses] = match (true) {
                    $task->isOverdue() => [__('Overdue'), 'bg-amber-100 text-amber-700'],
                    $dueAt?->isToday() ?? false => [__('Due Today'), 'bg-sky-100 text-sky-700'],
                    default => [__('Upcoming'), 'bg-slate-100 text-slate-700'],
                };
            @endphp

            <div class="rounded-xl border border-slate-100 bg-slate-50/70 px-2.5 py-2 shadow-sm shadow-slate-200/40">
                <div class="flex items-center gap-2">
                    <div class="min-w-0 flex-1">
                        <button
                            type="button"
                            class="block max-w-full truncate text-start text-xs font-semibold text-black hover:underline"
                            @click="$dispatch('open-modal', 'task-{{ $task->id }}')"
                        >
                            {{ $task->title }}
                        </button>

                        <p class="mt-0.5 truncate text-[11px] leading-tight text-slate-500">
                            @if ($task->lead)
                                <button
                                    type="button"
                                    class="font-medium text-slate-600 hover:text-navy hover:underline"
                                    @click="$dispatch('open-lead', {{ $task->lead->id }})"
                                >
                                    {{ $task->lead->name }}
                                </button>
                            @endif
                            @if ($timeLabel)
                                @if ($task->lead)
                                    <span class="text-slate-300">·</span>
                                @endif
                                {{ $timeLabel }}
                            @endif
                        </p>
                    </div>

                    <div class="flex shrink-0 flex-wrap items-center justify-center gap-1">
                        <x-tenant.task-status-badge :task="$task" class="!px-2 !py-0.5 !text-[10px]" />
                        <span @class([
                            'inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-semibold',
                            $dueClasses,
                        ])>
                            {{ $dueLabel }}
                        </span>
                    </div>

                    <div class="shrink-0">
                        @include('tenant.tasks.partials.task-status-actions', [
                            'task' => $task,
                            'filter' => $todayFilter,
                        ])
                    </div>
                </div>
            </div>
        @empty
            <div class="flex h-full items-center justify-center rounded-xl border border-dashed border-slate-200 bg-slate-50/50 px-3">
                <p class="text-center text-xs text-slate-500">{{ __('No tasks for today.') }}</p>
            </div>
        @endforelse
    </div>
</section>

@push('modals')
    @foreach ($tasks as $task)
        @include('tenant.tasks.partials.task-details-modal', [
            'task' => $task,
            'filter' => $todayFilter,
        ])
    @endforeach
@endpush
