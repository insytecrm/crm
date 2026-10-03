<?php

namespace App\Queries;

use App\Models\LeadTask;
use Illuminate\Support\Collection;

class DashboardTodaysTasks
{
    public function __construct(
        private TaskListing $taskListing,
    ) {}

    /**
     * Today's incomplete tasks, including overdue items.
     *
     * @return Collection<int, LeadTask>
     */
    public function forTenant(): Collection
    {
        return $this->taskListing
            ->openDueToday()
            ->sortBy(fn (LeadTask $task): array => [
                $task->isOverdue() ? 0 : 1,
                ($task->due_at ?? $task->created_at)?->getTimestamp() ?? 0,
            ])
            ->values();
    }
}
