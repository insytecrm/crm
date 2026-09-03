<?php

namespace App\Http\Controllers\Tenant;

use App\Actions\CompleteTask;
use App\Actions\LogLeadActivity;
use App\Actions\UpdateTaskStatus;
use App\Enums\LeadActivityType;
use App\Enums\TaskStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\CompleteScheduledActivityRequest;
use App\Http\Requests\Tenant\StoreLeadTaskRequest;
use App\Http\Requests\Tenant\UpdateLeadTaskStatusRequest;
use App\Models\Lead;
use App\Models\LeadTask;
use App\Support\LeadDrawerRedirect;
use Illuminate\Http\RedirectResponse;

class LeadTaskController extends Controller
{
    public function store(StoreLeadTaskRequest $request, Lead $lead, LogLeadActivity $logLeadActivity): RedirectResponse
    {
        $task = $lead->tasks()->create([
            ...$request->validated(),
            'status' => TaskStatus::Pending,
            'created_by_id' => auth()->id(),
            'assigned_to_id' => $request->validated('assigned_to_id') ?? auth()->id(),
        ]);

        $logLeadActivity->handle(
            $lead,
            LeadActivityType::TaskCreated,
            __('Task created: :title', ['title' => $task->title]),
            metadata: ['task_id' => $task->id],
        );

        return LeadDrawerRedirect::to($lead, __('Task created.'));
    }

    public function updateStatus(
        UpdateLeadTaskStatusRequest $request,
        Lead $lead,
        LeadTask $task,
        UpdateTaskStatus $updateTaskStatus,
        LogLeadActivity $logLeadActivity,
    ): RedirectResponse {
        abort_unless($task->lead_id === $lead->id, 404);

        $status = TaskStatus::from($request->validated('status'));

        abort_if($status === TaskStatus::Complete, 404);
        abort_unless($task->status->canTransitionTo($status), 404);

        $updateTaskStatus->handle($task, $status);

        $message = match ($status) {
            TaskStatus::InProgress => __('Task started.'),
            TaskStatus::Cancelled => __('Task cancelled.'),
            default => __('Task updated.'),
        };

        $logLeadActivity->handle(
            $lead,
            LeadActivityType::TaskCreated,
            __('Task :status: :title', ['status' => strtolower($status->label()), 'title' => $task->title]),
            metadata: ['task_id' => $task->id],
        );

        return LeadDrawerRedirect::to($lead, $message);
    }

    public function complete(
        CompleteScheduledActivityRequest $request,
        Lead $lead,
        LeadTask $task,
        CompleteTask $completeTask,
        LogLeadActivity $logLeadActivity,
    ): RedirectResponse {
        abort_unless($task->lead_id === $lead->id, 404);
        abort_unless($task->status->canTransitionTo(TaskStatus::Complete), 404);

        $notes = $request->validated('notes');

        $completeTask->handle($task, $notes);

        $description = filled($notes)
            ? __('Task completed: :title — :notes', ['title' => $task->title, 'notes' => $notes])
            : __('Task completed: :title', ['title' => $task->title]);

        $metadata = ['task_id' => $task->id];

        if (filled($notes)) {
            $metadata['completion_notes'] = $notes;
        }

        $logLeadActivity->handle(
            $lead,
            LeadActivityType::TaskCompleted,
            $description,
            metadata: $metadata,
        );

        return LeadDrawerRedirect::to($lead, __('Task marked complete.'));
    }
}
