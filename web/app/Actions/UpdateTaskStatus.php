<?php

namespace App\Actions;

use App\Enums\TaskStatus;
use App\Models\LeadTask;

class UpdateTaskStatus
{
    public function handle(LeadTask $task, TaskStatus $status, ?string $notes = null): LeadTask
    {
        abort_unless($task->status->canTransitionTo($status), 422);

        $attributes = ['status' => $status];

        if ($status === TaskStatus::Complete) {
            $attributes['completed_at'] = now();
        }

        if ($status === TaskStatus::Cancelled) {
            $attributes['cancellation_notes'] = $notes;
        }

        $task->update($attributes);

        return $task->refresh();
    }
}
