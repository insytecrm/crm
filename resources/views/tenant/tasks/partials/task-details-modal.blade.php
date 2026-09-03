<x-modal :name="'task-'.$task->id" maxWidth="2xl">
    <div class="p-6">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h2 class="text-lg font-bold text-black {{ $task->isClosed() ? 'line-through text-slate-400' : '' }}">{{ $task->title }}</h2>
                <p class="mt-1 text-sm text-slate-500">{{ __('Linked to') }} <x-tenant.lead-link :lead="$task->lead" /></p>
            </div>
            <x-tenant.task-status-badge :task="$task" class="shrink-0" />
        </div>

        <div class="mt-5">
            <p class="mb-2 text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Timeline') }}</p>
            <x-tenant.task-status-timeline :task="$task" />
        </div>

        <dl class="mt-6 grid gap-4 sm:grid-cols-2">
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Assigned To') }}</dt>
                <dd class="mt-1 text-sm text-black">{{ $task->assignedTo?->name ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Due Date') }}</dt>
                <dd class="mt-1 text-sm {{ $task->isOverdue() ? 'font-medium text-rose-600' : 'text-black' }}">
                    @if ($task->due_at)
                        {{ $task->due_at->format('M j, Y g:i A') }}
                    @else
                        —
                    @endif
                </dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Created By') }}</dt>
                <dd class="mt-1 text-sm text-black">{{ $task->createdBy?->name ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Created At') }}</dt>
                <dd class="mt-1 text-sm text-black">{{ $task->created_at?->format('M j, Y g:i A') }}</dd>
            </div>
            @if ($task->isCompleted())
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Completed At') }}</dt>
                    <dd class="mt-1 text-sm text-black">{{ $task->completed_at?->format('M j, Y g:i A') }}</dd>
                </div>
                @if ($task->completion_notes)
                    <div class="sm:col-span-2">
                        <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Completion Notes') }}</dt>
                        <dd class="mt-1 whitespace-pre-wrap text-sm text-slate-700">{{ $task->completion_notes }}</dd>
                    </div>
                @endif
            @endif
            @if ($task->description)
                <div class="sm:col-span-2">
                    <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Description') }}</dt>
                    <dd class="mt-1 whitespace-pre-wrap text-sm text-slate-700">{{ $task->description }}</dd>
                </div>
            @endif
        </dl>

        <div class="mt-6 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
            <x-ui.button type="button" variant="outline" @click="$dispatch('close-modal', 'task-{{ $task->id }}')">{{ __('Close') }}</x-ui.button>
            @include('tenant.tasks.partials.task-status-actions', ['task' => $task, 'filter' => $filter])
        </div>
    </div>
</x-modal>
