<?php

namespace App\Actions;

use App\Enums\InsyteAiTool;
use App\Enums\LeadActivityType;
use App\Enums\LeadScheduledEventStatus;
use App\Enums\LeadScheduledEventType;
use App\Enums\PlanCapability;
use App\Enums\ScheduledActivityPriority;
use App\Enums\SiteVisitType;
use App\Enums\TaskStatus;
use App\Enums\TenantPermission;
use App\Models\Lead;
use App\Models\LeadScheduledEvent;
use App\Models\LeadTask;
use App\Models\Property;
use App\Models\User;
use App\Queries\DashboardTodaysTasks;
use App\Support\Platform\TenantPlanAccess;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ExecuteInsyteAiTool
{
    public function __construct(
        private RecordLeadScheduledEvent $recordLeadScheduledEvent,
        private LogLeadActivity $logLeadActivity,
        private CompleteTask $completeTask,
        private DashboardTodaysTasks $dashboardTodaysTasks,
        private TenantPlanAccess $planAccess,
    ) {}

    /**
     * @param  array<string, mixed>  $arguments
     * @return array{ok: bool, mutated: bool, message: string, lead_url: ?string}
     */
    public function handle(string $name, array $arguments, User $user): array
    {
        $tool = InsyteAiTool::tryFrom($name);

        if ($tool === null) {
            return $this->failure(__('I cannot run that action.'));
        }

        if (! $user->hasPermission($tool->permission())) {
            return $this->failure(__('You do not have permission to do that.'));
        }

        if (! $this->planAccess->hasCapability(PlanCapability::fromInsyteAiTool($tool))) {
            return $this->failure(__('This action is not included in your plan.'));
        }

        return match ($tool) {
            InsyteAiTool::SearchLeads => $this->searchLeads($arguments),
            InsyteAiTool::GetLead => $this->getLead($arguments),
            InsyteAiTool::ListToday => $this->listToday($user),
            InsyteAiTool::ScheduleFollowUp => $this->scheduleFollowUp($arguments, $user),
            InsyteAiTool::ScheduleSiteVisit => $this->scheduleSiteVisit($arguments, $user),
            InsyteAiTool::CompleteFollowUp => $this->completeFollowUp($arguments),
            InsyteAiTool::CompleteSiteVisit => $this->completeSiteVisit($arguments),
            InsyteAiTool::CreateTask => $this->createTask($arguments, $user),
            InsyteAiTool::CompleteTask => $this->completeLeadTask($arguments),
            InsyteAiTool::AddNote => $this->addNote($arguments, $user),
        };
    }

    /**
     * @param  array<string, mixed>  $arguments
     * @return array{ok: bool, mutated: bool, message: string, lead_url: ?string}
     */
    private function searchLeads(array $arguments): array
    {
        $query = trim((string) ($arguments['query'] ?? ''));

        if ($query === '') {
            return $this->failure(__('Tell me a name, phone, or email to search for.'));
        }

        $leads = Lead::query()
            ->where(function ($builder) use ($query): void {
                $builder->where('name', 'like', '%'.$query.'%')
                    ->orWhere('phone', 'like', '%'.$query.'%')
                    ->orWhere('email', 'like', '%'.$query.'%');
            })
            ->orderBy('name')
            ->limit(8)
            ->get(['id', 'name', 'phone', 'status']);

        if ($leads->isEmpty()) {
            return $this->failure(__('No matching leads found.'));
        }

        $lines = $leads->map(function (Lead $lead): string {
            return '#'.$lead->id.' '.$lead->name.' ('.$lead->status->label().')'.($lead->phone ? ' '.$lead->phone : '');
        });

        return $this->success($lines->implode("\n"), mutated: false);
    }

    /**
     * @param  array<string, mixed>  $arguments
     * @return array{ok: bool, mutated: bool, message: string, lead_url: ?string}
     */
    private function getLead(array $arguments): array
    {
        $lead = $this->findLead($arguments);

        if ($lead === null) {
            return $this->failure(__('I could not find that lead.'));
        }

        $followUp = $lead->scheduledEvents
            ->first(fn (LeadScheduledEvent $event): bool => $event->type === LeadScheduledEventType::FollowUp
                && $event->status === LeadScheduledEventStatus::Scheduled);

        $siteVisit = $lead->scheduledEvents
            ->first(fn (LeadScheduledEvent $event): bool => $event->type === LeadScheduledEventType::SiteVisit
                && $event->status === LeadScheduledEventStatus::Scheduled);

        $openTasks = $lead->tasks
            ->reject(fn (LeadTask $task): bool => $task->isClosed())
            ->map(fn (LeadTask $task): string => '#'.$task->id.' '.$task->title)
            ->values();

        $parts = [
            __('Lead #:id :name', ['id' => $lead->id, 'name' => $lead->name]),
            __('Status: :status', ['status' => $lead->status->label()]),
        ];

        if ($lead->phone) {
            $parts[] = __('Phone: :phone', ['phone' => $lead->phone]);
        }

        $parts[] = $followUp
            ? __('Open follow-up: :date', ['date' => $followUp->scheduled_at?->format('M j, Y g:i A')])
            : __('No open follow-up.');

        $parts[] = $siteVisit
            ? __('Open site visit: :date', ['date' => $siteVisit->scheduled_at?->format('M j, Y g:i A')])
            : __('No open site visit.');

        $parts[] = $openTasks->isNotEmpty()
            ? __('Open tasks: :tasks', ['tasks' => $openTasks->implode(', ')])
            : __('No open tasks.');

        return $this->success(implode("\n", $parts), mutated: false, lead: $lead);
    }

    /**
     * @return array{ok: bool, mutated: bool, message: string, lead_url: ?string}
     */
    private function listToday(User $user): array
    {
        if (! $user->hasPermission(TenantPermission::ActivitiesView) && ! $user->hasPermission(TenantPermission::TasksView)) {
            return $this->failure(__('You do not have permission to do that.'));
        }

        $followUps = LeadScheduledEvent::query()
            ->with('lead')
            ->where('type', LeadScheduledEventType::FollowUp)
            ->where('status', LeadScheduledEventStatus::Scheduled)
            ->where('scheduled_at', '<=', now()->endOfDay())
            ->whereHas('lead')
            ->orderBy('scheduled_at')
            ->limit(8)
            ->get();

        $siteVisits = LeadScheduledEvent::query()
            ->with('lead')
            ->where('type', LeadScheduledEventType::SiteVisit)
            ->where('status', LeadScheduledEventStatus::Scheduled)
            ->where('scheduled_at', '<=', now()->endOfDay())
            ->whereHas('lead')
            ->orderBy('scheduled_at')
            ->limit(8)
            ->get();

        $tasks = $this->dashboardTodaysTasks->forTenant()->take(8);

        $lines = [];

        foreach ($followUps as $event) {
            $lines[] = __('Follow-up: :name at :date', [
                'name' => $event->lead?->name ?? __('Lead'),
                'date' => $event->scheduled_at?->format('M j, Y g:i A'),
            ]);
        }

        foreach ($siteVisits as $event) {
            $lines[] = __('Site visit: :name at :date', [
                'name' => $event->lead?->name ?? __('Lead'),
                'date' => $event->scheduled_at?->format('M j, Y g:i A'),
            ]);
        }

        foreach ($tasks as $task) {
            $lines[] = __('Task: :title (:name)', [
                'title' => $task->title,
                'name' => $task->lead?->name ?? __('Lead'),
            ]);
        }

        if ($lines === []) {
            return $this->success(__('Nothing is due today.'), mutated: false);
        }

        return $this->success(implode("\n", $lines), mutated: false);
    }

    /**
     * @param  array<string, mixed>  $arguments
     * @return array{ok: bool, mutated: bool, message: string, lead_url: ?string}
     */
    private function scheduleFollowUp(array $arguments, User $user): array
    {
        $lead = $this->findLead($arguments);

        if ($lead === null) {
            return $this->failure(__('I could not find that lead.'));
        }

        $scheduledAt = $this->parseDate($arguments['scheduled_at'] ?? null);

        if ($scheduledAt === null) {
            return $this->failure(__('Tell me when to schedule the follow-up.'));
        }

        $priority = ScheduledActivityPriority::tryFrom((string) ($arguments['priority'] ?? ''))
            ?? ScheduledActivityPriority::Normal;
        $notes = $this->nullableString($arguments['notes'] ?? null);

        $event = DB::transaction(function () use ($lead, $scheduledAt, $notes, $priority, $user) {
            return $this->recordLeadScheduledEvent->schedule(
                $lead,
                LeadScheduledEventType::FollowUp,
                $scheduledAt,
                $notes,
                $priority,
                user: $user,
            );
        });

        $description = $notes
            ? __(':label scheduled for :date — :notes', [
                'label' => $event->ordinalLabel(),
                'date' => $scheduledAt->format('M j, Y g:i A'),
                'notes' => $notes,
            ])
            : __(':label scheduled for :date', [
                'label' => $event->ordinalLabel(),
                'date' => $scheduledAt->format('M j, Y g:i A'),
            ]);

        $this->logLeadActivity->handle(
            $lead,
            LeadActivityType::FollowUpScheduled,
            $description,
            $user,
            metadata: [
                'scheduled_event_id' => $event->id,
                'sequence_number' => $event->sequence_number,
                'priority' => $priority->value,
                'source' => 'insyte_ai',
            ],
        );

        return $this->success(
            __('Follow-up scheduled for :name on :date.', [
                'name' => $lead->name,
                'date' => $scheduledAt->format('M j, Y g:i A'),
            ]),
            mutated: true,
            lead: $lead,
        );
    }

    /**
     * @param  array<string, mixed>  $arguments
     * @return array{ok: bool, mutated: bool, message: string, lead_url: ?string}
     */
    private function scheduleSiteVisit(array $arguments, User $user): array
    {
        $lead = $this->findLead($arguments);

        if ($lead === null) {
            return $this->failure(__('I could not find that lead.'));
        }

        $scheduledAt = $this->parseDate($arguments['scheduled_at'] ?? null);

        if ($scheduledAt === null) {
            return $this->failure(__('Tell me when to schedule the site visit.'));
        }

        $visitType = SiteVisitType::tryFrom((string) ($arguments['visit_type'] ?? ''));
        $propertyId = isset($arguments['property_id']) ? (int) $arguments['property_id'] : 0;
        $property = $propertyId > 0
            ? Property::query()->where('is_active', true)->find($propertyId)
            : null;

        if ($property === null || $visitType === null) {
            $properties = Property::query()
                ->where('is_active', true)
                ->orderBy('project_name')
                ->limit(8)
                ->get(['id', 'project_name']);

            $list = $properties->map(fn (Property $item): string => '#'.$item->id.' '.$item->project_name)->implode(', ');

            return $this->failure(
                __('A property and visit type are required. Active properties: :properties. Visit types: fresh_visit, revisit.', [
                    'properties' => $list !== '' ? $list : __('none'),
                ]),
            );
        }

        $notes = $this->nullableString($arguments['notes'] ?? null);

        $event = DB::transaction(function () use ($lead, $scheduledAt, $notes, $property, $visitType, $user) {
            return $this->recordLeadScheduledEvent->schedule(
                $lead,
                LeadScheduledEventType::SiteVisit,
                $scheduledAt,
                $notes,
                propertyId: $property->id,
                visitType: $visitType,
                user: $user,
            );
        });

        $description = $notes
            ? __(':label scheduled for :date — :notes', [
                'label' => $event->ordinalLabel(),
                'date' => $scheduledAt->format('M j, Y g:i A'),
                'notes' => $notes,
            ])
            : __(':label scheduled for :date', [
                'label' => $event->ordinalLabel(),
                'date' => $scheduledAt->format('M j, Y g:i A'),
            ]);

        $this->logLeadActivity->handle(
            $lead,
            LeadActivityType::SiteVisitScheduled,
            $description,
            $user,
            metadata: [
                'scheduled_event_id' => $event->id,
                'sequence_number' => $event->sequence_number,
                'property_id' => $property->id,
                'visit_type' => $visitType->value,
                'source' => 'insyte_ai',
            ],
        );

        return $this->success(
            __('Site visit scheduled for :name at :property on :date.', [
                'name' => $lead->name,
                'property' => $property->project_name,
                'date' => $scheduledAt->format('M j, Y g:i A'),
            ]),
            mutated: true,
            lead: $lead,
        );
    }

    /**
     * @param  array<string, mixed>  $arguments
     * @return array{ok: bool, mutated: bool, message: string, lead_url: ?string}
     */
    private function completeFollowUp(array $arguments): array
    {
        $lead = $this->findLead($arguments);

        if ($lead === null) {
            return $this->failure(__('I could not find that lead.'));
        }

        if ($lead->next_follow_up_at === null) {
            return $this->failure(__('No follow-up is scheduled for this lead.'));
        }

        $notes = $this->nullableString($arguments['notes'] ?? null);
        $event = $this->recordLeadScheduledEvent->completeLatest($lead, LeadScheduledEventType::FollowUp);

        $description = match (true) {
            $event !== null && filled($notes) => __(':label completed — :notes', [
                'label' => $event->ordinalLabel(),
                'notes' => $notes,
            ]),
            $event !== null => __(':label completed', ['label' => $event->ordinalLabel()]),
            filled($notes) => __('Follow-up completed — :notes', ['notes' => $notes]),
            default => __('Follow-up completed'),
        };

        $metadata = $event ? [
            'scheduled_event_id' => $event->id,
            'sequence_number' => $event->sequence_number,
            'source' => 'insyte_ai',
        ] : ['source' => 'insyte_ai'];

        if (filled($notes)) {
            $metadata['completion_notes'] = $notes;
        }

        $this->logLeadActivity->handle(
            $lead,
            LeadActivityType::FollowUpCompleted,
            $description,
            metadata: $metadata,
        );

        return $this->success(
            __('Follow-up marked complete for :name.', ['name' => $lead->name]),
            mutated: true,
            lead: $lead,
        );
    }

    /**
     * @param  array<string, mixed>  $arguments
     * @return array{ok: bool, mutated: bool, message: string, lead_url: ?string}
     */
    private function completeSiteVisit(array $arguments): array
    {
        $lead = $this->findLead($arguments);

        if ($lead === null) {
            return $this->failure(__('I could not find that lead.'));
        }

        if ($lead->upcoming_site_visit_at === null) {
            return $this->failure(__('No site visit is scheduled for this lead.'));
        }

        $notes = $this->nullableString($arguments['notes'] ?? null);
        $event = $this->recordLeadScheduledEvent->completeLatest($lead, LeadScheduledEventType::SiteVisit);

        $description = match (true) {
            $event !== null && filled($notes) => __(':label completed — :notes', [
                'label' => $event->ordinalLabel(),
                'notes' => $notes,
            ]),
            $event !== null => __(':label completed', ['label' => $event->ordinalLabel()]),
            filled($notes) => __('Site visit completed — :notes', ['notes' => $notes]),
            default => __('Site visit completed'),
        };

        $metadata = $event ? [
            'scheduled_event_id' => $event->id,
            'sequence_number' => $event->sequence_number,
            'source' => 'insyte_ai',
        ] : ['source' => 'insyte_ai'];

        if (filled($notes)) {
            $metadata['completion_notes'] = $notes;
        }

        $this->logLeadActivity->handle(
            $lead,
            LeadActivityType::SiteVisitCompleted,
            $description,
            metadata: $metadata,
        );

        return $this->success(
            __('Site visit marked complete for :name.', ['name' => $lead->name]),
            mutated: true,
            lead: $lead,
        );
    }

    /**
     * @param  array<string, mixed>  $arguments
     * @return array{ok: bool, mutated: bool, message: string, lead_url: ?string}
     */
    private function createTask(array $arguments, User $user): array
    {
        $lead = $this->findLead($arguments);

        if ($lead === null) {
            return $this->failure(__('I could not find that lead.'));
        }

        $title = trim((string) ($arguments['title'] ?? ''));

        if ($title === '') {
            return $this->failure(__('Tell me the task title.'));
        }

        $task = $lead->tasks()->create([
            'title' => $title,
            'description' => $this->nullableString($arguments['description'] ?? null),
            'due_at' => $this->parseDate($arguments['due_at'] ?? null),
            'status' => TaskStatus::Pending,
            'created_by_id' => $user->id,
            'assigned_to_id' => $user->id,
        ]);

        $this->logLeadActivity->handle(
            $lead,
            LeadActivityType::TaskCreated,
            __('Task created: :title', ['title' => $task->title]),
            $user,
            metadata: [
                'task_id' => $task->id,
                'source' => 'insyte_ai',
            ],
        );

        return $this->success(
            __('Task created for :name: :title.', [
                'name' => $lead->name,
                'title' => $task->title,
            ]),
            mutated: true,
            lead: $lead,
        );
    }

    /**
     * @param  array<string, mixed>  $arguments
     * @return array{ok: bool, mutated: bool, message: string, lead_url: ?string}
     */
    private function completeLeadTask(array $arguments): array
    {
        $taskId = (int) ($arguments['task_id'] ?? 0);
        $task = $taskId > 0 ? LeadTask::query()->with('lead')->find($taskId) : null;

        if ($task === null || $task->lead === null) {
            return $this->failure(__('I could not find that task.'));
        }

        if (! $task->status->canTransitionTo(TaskStatus::Complete) && $task->status !== TaskStatus::InProgress) {
            if ($task->status !== TaskStatus::Pending) {
                return $this->failure(__('That task cannot be marked complete.'));
            }
        }

        if ($task->status === TaskStatus::Pending) {
            $task->update(['status' => TaskStatus::InProgress]);
            $task->refresh();
        }

        if (! $task->status->canTransitionTo(TaskStatus::Complete)) {
            return $this->failure(__('That task cannot be marked complete.'));
        }

        $notes = $this->nullableString($arguments['notes'] ?? null);
        $this->completeTask->handle($task, $notes);

        $description = filled($notes)
            ? __('Task completed: :title — :notes', ['title' => $task->title, 'notes' => $notes])
            : __('Task completed: :title', ['title' => $task->title]);

        $this->logLeadActivity->handle(
            $task->lead,
            LeadActivityType::TaskCompleted,
            $description,
            metadata: [
                'task_id' => $task->id,
                'source' => 'insyte_ai',
            ],
        );

        return $this->success(
            __('Task marked complete: :title.', ['title' => $task->title]),
            mutated: true,
            lead: $task->lead,
        );
    }

    /**
     * @param  array<string, mixed>  $arguments
     * @return array{ok: bool, mutated: bool, message: string, lead_url: ?string}
     */
    private function addNote(array $arguments, User $user): array
    {
        $lead = $this->findLead($arguments);

        if ($lead === null) {
            return $this->failure(__('I could not find that lead.'));
        }

        $body = trim((string) ($arguments['body'] ?? ''));

        if ($body === '') {
            return $this->failure(__('Tell me what to write in the note.'));
        }

        $note = $lead->notes()->create([
            'body' => $body,
            'user_id' => $user->id,
        ]);

        $this->logLeadActivity->handle(
            $lead,
            LeadActivityType::NoteAdded,
            __('Note added'),
            $user,
            metadata: [
                'note_id' => $note->id,
                'body' => $note->body,
                'source' => 'insyte_ai',
            ],
        );

        return $this->success(
            __('Note added on :name.', ['name' => $lead->name]),
            mutated: true,
            lead: $lead,
        );
    }

    /**
     * @param  array<string, mixed>  $arguments
     */
    private function findLead(array $arguments): ?Lead
    {
        $leadId = (int) ($arguments['lead_id'] ?? 0);

        if ($leadId <= 0) {
            return null;
        }

        return Lead::query()
            ->with(['scheduledEvents', 'tasks'])
            ->find($leadId);
    }

    private function parseDate(mixed $value): ?Carbon
    {
        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        try {
            return Carbon::parse(str_replace('T', ' ', $value), config('app.timezone'));
        } catch (\Throwable) {
            return null;
        }
    }

    private function nullableString(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $value = trim($value);

        return $value === '' ? null : $value;
    }

    /**
     * @return array{ok: bool, mutated: bool, message: string, lead_url: ?string}
     */
    private function success(string $message, bool $mutated, ?Lead $lead = null): array
    {
        return [
            'ok' => true,
            'mutated' => $mutated,
            'message' => $message,
            'lead_url' => $lead ? route('tenant.leads.index', ['lead' => $lead->id]) : null,
        ];
    }

    /**
     * @return array{ok: bool, mutated: bool, message: string, lead_url: ?string}
     */
    private function failure(string $message): array
    {
        return [
            'ok' => false,
            'mutated' => false,
            'message' => $message,
            'lead_url' => null,
        ];
    }
}
