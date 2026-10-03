<?php

namespace App\Http\Controllers\Tenant;

use App\Actions\CompleteTask;
use App\Actions\LogLeadActivity;
use App\Actions\UpdateTaskStatus;
use App\Enums\LeadActivityType;
use App\Enums\TaskFilter;
use App\Enums\TaskStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\CompleteScheduledActivityRequest;
use App\Http\Requests\Tenant\StoreTaskRequest;
use App\Http\Requests\Tenant\UpdateTaskStatusRequest;
use App\Models\Lead;
use App\Models\LeadTask;
use App\Models\User;
use App\Queries\TaskListing;
use App\Support\DataTable\DataTableViewData;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TaskController extends Controller
{
    public function index(Request $request, TaskListing $taskListing): View
    {
        $filter = TaskFilter::fromRequest($request->string('filter')->toString());
        $search = $request->string('search')->trim()->toString();

        $tasks = $taskListing->items($filter, $search);

        return view('tenant.tasks.index', array_merge([
            'filter' => $filter,
            'filters' => TaskFilter::cases(),
            'search' => $search,
            'statistics' => $taskListing->statistics(),
            'tasks' => $tasks,
            'leads' => Lead::query()->orderBy('name')->get(['id', 'name']),
            'users' => User::query()->orderBy('name')->get(['id', 'name']),
        ], DataTableViewData::for($request->user(), 'tasks', $tasks, 'id', $filter->value)));
    }

    public function store(StoreTaskRequest $request, LogLeadActivity $logLeadActivity): RedirectResponse
    {
        $lead = Lead::query()->findOrFail($request->validated('lead_id'));

        $task = $lead->tasks()->create([
            'title' => $request->validated('title'),
            'description' => $request->validated('description'),
            'due_at' => $request->validated('due_at'),
            'status' => TaskStatus::Pending,
            'assigned_to_id' => $request->validated('assigned_to_id') ?? auth()->id(),
            'created_by_id' => auth()->id(),
        ]);

        $logLeadActivity->handle(
            $lead,
            LeadActivityType::TaskCreated,
            __('Task created: :title', ['title' => $task->title]),
            metadata: ['task_id' => $task->id],
        );

        return redirect()
            ->route('tenant.tasks.index', ['filter' => TaskFilter::All->value])
            ->with('status', __('Task created.'));
    }

    public function updateStatus(
        UpdateTaskStatusRequest $request,
        LeadTask $task,
        UpdateTaskStatus $updateTaskStatus,
        LogLeadActivity $logLeadActivity,
    ): RedirectResponse {
        $status = TaskStatus::from($request->validated('status'));

        abort_if($status === TaskStatus::Complete, 404);

        return $this->transition(
            $task,
            $status,
            $request->validated('notes'),
            $updateTaskStatus,
            $logLeadActivity,
        );
    }

    public function complete(
        CompleteScheduledActivityRequest $request,
        LeadTask $task,
        CompleteTask $completeTask,
        LogLeadActivity $logLeadActivity,
    ): RedirectResponse {
        abort_unless($task->status->canTransitionTo(TaskStatus::Complete), 404);

        $notes = $request->validated('notes');

        $completeTask->handle($task, $notes);

        $this->logTaskCompleted($task, $notes, $logLeadActivity);

        return back()->with('status', __('Task marked complete.'));
    }

    private function transition(
        LeadTask $task,
        TaskStatus $status,
        ?string $notes,
        UpdateTaskStatus $updateTaskStatus,
        LogLeadActivity $logLeadActivity,
    ): RedirectResponse {
        abort_unless($task->status->canTransitionTo($status), 404);

        $updateTaskStatus->handle($task, $status, $notes);

        $message = match ($status) {
            TaskStatus::InProgress => __('Task started.'),
            TaskStatus::Cancelled => __('Task cancelled.'),
            default => __('Task updated.'),
        };

        if ($task->lead) {
            $description = $status === TaskStatus::Cancelled && filled($notes)
                ? __('Task cancelled: :title — :notes', ['title' => $task->title, 'notes' => $notes])
                : __('Task :status: :title', ['status' => strtolower($status->label()), 'title' => $task->title]);

            $metadata = ['task_id' => $task->id];

            if ($status === TaskStatus::Cancelled && filled($notes)) {
                $metadata['cancellation_notes'] = $notes;
            }

            $logLeadActivity->handle(
                $task->lead,
                LeadActivityType::TaskCreated,
                $description,
                metadata: $metadata,
            );
        }

        return back()->with('status', $message);
    }

    private function logTaskCompleted(LeadTask $task, ?string $notes, LogLeadActivity $logLeadActivity): void
    {
        if (! $task->lead) {
            return;
        }

        $description = filled($notes)
            ? __('Task completed: :title — :notes', ['title' => $task->title, 'notes' => $notes])
            : __('Task completed: :title', ['title' => $task->title]);

        $metadata = ['task_id' => $task->id];

        if (filled($notes)) {
            $metadata['completion_notes'] = $notes;
        }

        $logLeadActivity->handle(
            $task->lead,
            LeadActivityType::TaskCompleted,
            $description,
            metadata: $metadata,
        );
    }
}
