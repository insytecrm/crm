<?php

namespace App\Actions;

use App\Enums\TaskStatus;
use App\Models\LeadTask;

class CompleteTask
{
    public function handle(LeadTask $task, ?string $notes = null): LeadTask
    {
        abort_unless($task->status->canTransitionTo(TaskStatus::Complete), 422);

        $task->update([
            'status' => TaskStatus::Complete,
            'completed_at' => now(),
            'completion_notes' => $notes,
        ]);

        return $task->refresh();
    }
}
