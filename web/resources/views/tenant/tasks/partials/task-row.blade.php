<tr class="align-middle transition hover:bg-slate-50/60">
    <x-tenant.manageable-table.checkbox-cell :id="$task->id" />
    <td x-show="isColumnVisible('title')" class="px-4 py-3 align-middle">
        <button
            type="button"
            class="text-start text-sm font-medium text-black hover:underline {{ $task->isClosed() ? 'line-through text-slate-400' : '' }}"
            @click="$dispatch('open-modal', 'task-{{ $task->id }}')"
        >
            {{ $task->title }}
        </button>
        @if ($task->description)
            <p class="mt-0.5 line-clamp-1 text-xs text-slate-500" x-show="!isColumnVisible('description')">{{ $task->description }}</p>
        @endif
    </td>
    <td x-show="isColumnVisible('related_to')" class="whitespace-nowrap px-4 py-3 align-middle text-sm font-medium text-black">
        <x-tenant.lead-link :lead="$task->lead" />
    </td>
    <td x-show="isColumnVisible('assigned_to')" class="whitespace-nowrap px-4 py-3 align-middle text-sm text-slate-600">
        {{ $task->assignedTo?->name ?? '—' }}
    </td>
    <td x-show="isColumnVisible('due_date')" class="whitespace-nowrap px-4 py-3 align-middle text-sm {{ $task->isOverdue() ? 'font-medium text-rose-600' : 'text-slate-600' }}">
        @if ($task->due_at)
            {{ $task->due_at->format('M j, Y g:i A') }}
        @else
            —
        @endif
    </td>
    <td x-show="isColumnVisible('status')" class="whitespace-nowrap px-4 py-3 align-middle">
        <x-tenant.task-status-badge :task="$task" />
    </td>
    <td x-show="isColumnVisible('description')" class="max-w-xs px-4 py-3 align-middle text-sm text-slate-600">
        <span class="line-clamp-2">{{ $task->description ?? '—' }}</span>
    </td>
    <x-tenant.manageable-table.custom-column-cells :record-id="$task->id" />
    <td x-show="isColumnVisible('actions')" class="whitespace-nowrap px-4 py-3 align-middle text-end">
        @include('tenant.tasks.partials.task-status-actions', ['task' => $task, 'filter' => $filter])
    </td>
</tr>
