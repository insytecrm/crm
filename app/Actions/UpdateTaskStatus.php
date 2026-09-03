<?php

namespace App\Actions;

use App\Enums\TaskStatus;
use App\Models\LeadTask;

class UpdateTaskStatus
{
    public function handle(LeadTask $task, TaskStatus $status): LeadTask
    {
        abort_unless($task->status->canTransitionTo($status), 422);

        $attributes = ['status' => $status];

        if ($status === TaskStatus::Complete) {
            $attributes['completed_at'] = now();
        }

        $task->update($attributes);

        return $task->refresh();
    }
}
