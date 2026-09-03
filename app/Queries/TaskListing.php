<?php

namespace App\Queries;

use App\Enums\TaskFilter;
use App\Enums\TaskStatus;
use App\Models\LeadTask;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class TaskListing
{
    /**
     * @return array{
     *     today: int,
     *     upcoming: int,
     *     completed: int,
     *     all: int,
     * }
     */
    public function statistics(): array
    {
        return [
            'today' => $this->baseQuery()->get()->filter(fn (LeadTask $task): bool => $this->matchesFilter($task, TaskFilter::Today))->count(),
            'upcoming' => $this->baseQuery()->get()->filter(fn (LeadTask $task): bool => $this->matchesFilter($task, TaskFilter::Upcoming))->count(),
            'completed' => $this->baseQuery()->where('status', TaskStatus::Complete)->count(),
            'all' => $this->baseQuery()->count(),
        ];
    }

    /**
     * @return Collection<int, LeadTask>
     */
    public function items(TaskFilter $filter): Collection
    {
        $tasks = $this->baseQuery()->get();

        return $tasks
            ->filter(fn (LeadTask $task): bool => $this->matchesFilter($task, $filter))
            ->sortBy(fn (LeadTask $task): int => $this->sortTimestamp($task, $filter))
            ->values();
    }

    /**
     * @return Builder<LeadTask>
     */
    private function baseQuery(): Builder
    {
        return LeadTask::query()
            ->with(['lead', 'assignedTo', 'createdBy']);
    }

    private function matchesFilter(LeadTask $task, TaskFilter $filter): bool
    {
        return match ($filter) {
            TaskFilter::Completed => $task->status === TaskStatus::Complete,
            TaskFilter::All => true,
            TaskFilter::Today => $this->matchesToday($task),
            TaskFilter::Upcoming => $this->matchesUpcoming($task),
        };
    }

    private function matchesToday(LeadTask $task): bool
    {
        if ($task->status === TaskStatus::Cancelled) {
            return false;
        }

        if ($task->status === TaskStatus::Complete) {
            return $task->completed_at?->isToday() ?? false;
        }

        if ($task->due_at === null) {
            return $task->created_at?->isToday() ?? false;
        }

        return $task->due_at->lte(now()->endOfDay());
    }

    private function matchesUpcoming(LeadTask $task): bool
    {
        if ($task->isClosed()) {
            return false;
        }

        if ($task->due_at === null) {
            return ! ($task->created_at?->isToday() ?? false);
        }

        return $task->due_at->gt(now()->endOfDay());
    }

    private function sortTimestamp(LeadTask $task, TaskFilter $filter): int
    {
        $timestamp = match (true) {
            $task->status === TaskStatus::Complete => $task->completed_at,
            $task->due_at !== null => $task->due_at,
            default => $task->created_at,
        };

        if ($timestamp === null) {
            return 0;
        }

        return match ($filter) {
            TaskFilter::Completed, TaskFilter::All => -1 * $timestamp->getTimestamp(),
            default => $timestamp->getTimestamp(),
        };
    }
}
