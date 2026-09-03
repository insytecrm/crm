@props([
    'task',
    'filter' => null,
    'statusAction' => null,
    'completeAction' => null,
    'compact' => true,
])

@php
    use App\Enums\TaskStatus;

    $filterValue = $filter instanceof \App\Enums\TaskFilter ? $filter->value : $filter;
    $statusRoute = $statusAction ?? route('tenant.tasks.status.update', $task);
    $completeRoute = $completeAction ?? route('tenant.tasks.complete', $task);

    $hiddenFields = collect(['filter' => $filterValue])
        ->filter(fn ($value) => filled($value))
        ->all();
@endphp

@if ($task->status->allowedTransitions() !== [])
    <x-ui.action-icon-group {{ $attributes }}>
        @foreach ($task->status->allowedTransitions() as $nextStatus)
            @if ($nextStatus === TaskStatus::Complete)
                @include('tenant.activities.partials.mark-complete-popover', [
                    'action' => $completeRoute,
                    'label' => $task->title,
                    'hidden' => $hiddenFields,
                ])
            @else
                @php
                    $label = match ($nextStatus) {
                        TaskStatus::InProgress => __('Start'),
                        TaskStatus::Cancelled => __('Cancel'),
                        default => $nextStatus->label(),
                    };

                    $icon = match ($nextStatus) {
                        TaskStatus::InProgress => 'play',
                        TaskStatus::Cancelled => 'cancel',
                        default => 'view',
                    };
                @endphp

                <form method="POST" action="{{ $statusRoute }}" class="inline">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="status" value="{{ $nextStatus->value }}">
                    @if ($filterValue)
                        <input type="hidden" name="filter" value="{{ $filterValue }}">
                    @endif
                    <x-ui.action-icon
                        :icon="$icon"
                        type="submit"
                        :compact="$compact"
                        :title="$label"
                    />
                </form>
            @endif
        @endforeach
    </x-ui.action-icon-group>
@endif
