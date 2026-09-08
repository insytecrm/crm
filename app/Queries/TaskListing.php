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
     *     total: int,
     *     pending: int,
     *     in_progress: int,
     *     completed: int,
     *     cancelled: int,
     * }
     */
    public function statistics(): array
    {
        $counts = $this->baseQuery()
            ->selectRaw('status, COUNT(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        return [
            'total' => (int) $counts->sum(),
            'pending' => (int) $counts->get(TaskStatus::Pending->value, 0),
            'in_progress' => (int) $counts->get(TaskStatus::InProgress->value, 0),
            'completed' => (int) $counts->get(TaskStatus::Complete->value, 0),
            'cancelled' => (int) $counts->get(TaskStatus::Cancelled->value, 0),
        ];
    }

    /**
     * @return Collection<int, LeadTask>
     */
    public function items(TaskFilter $filter, string $search = ''): Collection
    {
        $tasks = $this->baseQuery()->get();

        return $tasks
            ->filter(fn (LeadTask $task): bool => $this->matchesFilter($task, $filter))
            ->filter(fn (LeadTask $task): bool => $this->matchesSearch($task, $search))
            ->sortBy(fn (LeadTask $task): int => $this->sortTimestamp($task, $filter))
            ->values();
    }

    /**
     * Open tasks due today or overdue (used by the dashboard).
     *
     * @return Collection<int, LeadTask>
     */
    public function openDueToday(): Collection
    {
        return $this->baseQuery()
            ->get()
            ->filter(fn (LeadTask $task): bool => $this->matchesOpenDueToday($task))
            ->values();
    }

    private function matchesSearch(LeadTask $task, string $search): bool
    {
        if ($search === '') {
            return true;
        }

        $needle = mb_strtolower($search);

        return str_contains(mb_strtolower($task->title), $needle)
            || str_contains(mb_strtolower((string) $task->lead?->name), $needle)
            || str_contains(mb_strtolower((string) $task->assignedTo?->name), $needle);
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
            TaskFilter::All => true,
            TaskFilter::Pending => $task->status === TaskStatus::Pending,
            TaskFilter::InProgress => $task->status === TaskStatus::InProgress,
            TaskFilter::Completed => $task->status === TaskStatus::Complete,
            TaskFilter::Cancelled => $task->status === TaskStatus::Cancelled,
        };
    }

    private function matchesOpenDueToday(LeadTask $task): bool
    {
        if ($task->isClosed()) {
            return false;
        }

        if ($task->due_at === null) {
            return $task->created_at?->isToday() ?? false;
        }

        return $task->due_at->lte(now()->endOfDay());
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
            TaskFilter::Completed, TaskFilter::Cancelled, TaskFilter::All => -1 * $timestamp->getTimestamp(),
            default => $timestamp->getTimestamp(),
        };
    }
}
