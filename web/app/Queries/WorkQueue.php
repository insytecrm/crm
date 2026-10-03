<?php

namespace App\Queries;

use App\Enums\LeadStatus;
use App\Models\Lead;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class WorkQueue
{
    public function __construct(
        private DashboardMyDay $dashboardMyDay,
        private DashboardTodaysTasks $dashboardTodaysTasks,
        private StaleLeads $staleLeads,
    ) {}

    /**
     * @return Collection<int, array{
     *     id: string,
     *     queue_type: string,
     *     kind: string,
     *     label: string,
     *     occurred_at: CarbonInterface,
     *     is_overdue: bool,
     *     lead: Lead,
     *     intent: ?string,
     *     property_label: ?string,
     * }>
     */
    public function itemsFor(User $user, ?string $filter = null): Collection
    {
        $items = collect();

        foreach ($this->dashboardMyDay->forTenant() as $activity) {
            /** @var Lead $lead */
            $lead = $activity['lead'];

            if ($lead->assigned_to_id !== null && $lead->assigned_to_id !== $user->id && ! $user->isManagerRole()) {
                continue;
            }

            $kind = $activity['kind'];
            $intent = $kind === 'follow_up' ? 'complete-follow-up' : 'complete-site-visit';

            $items->push([
                'id' => 'event:'.$activity['event_id'],
                'queue_type' => 'scheduled',
                'kind' => $kind,
                'label' => $activity['label'],
                'occurred_at' => $activity['occurred_at'],
                'is_overdue' => (bool) $activity['is_overdue'],
                'lead' => $lead,
                'intent' => $intent,
                'property_label' => $activity['property_label'] ?? null,
            ]);
        }

        foreach ($this->dashboardTodaysTasks->forTenant() as $task) {
            if ($task->assigned_to_id !== $user->id) {
                continue;
            }

            $lead = $task->lead;

            if (! $lead instanceof Lead) {
                continue;
            }

            $items->push([
                'id' => 'task:'.$task->id,
                'queue_type' => 'task',
                'kind' => 'task',
                'label' => $task->title,
                'occurred_at' => $task->due_at ?? $task->created_at,
                'is_overdue' => $task->isOverdue(),
                'lead' => $lead,
                'intent' => 'tasks',
                'property_label' => null,
            ]);
        }

        $items = $items
            ->sortBy(fn (array $item): array => [
                $item['is_overdue'] ? 0 : 1,
                $item['occurred_at']->getTimestamp(),
            ])
            ->values();

        if ($filter === null || $filter === '' || $filter === 'all') {
            return $items;
        }

        return $items
            ->filter(fn (array $item): bool => $item['kind'] === $filter || ($filter === 'overdue' && $item['is_overdue']))
            ->values();
    }

    /**
     * @return array{
     *     unassigned: Collection<int, Lead>,
     *     stale: Collection<int, Lead>,
     * }
     */
    public function managerTriageFor(User $user): array
    {
        if (! $user->isManagerRole()) {
            return [
                'unassigned' => collect(),
                'stale' => collect(),
            ];
        }

        $unassigned = Lead::query()
            ->unassigned()
            ->whereNotIn('status', [LeadStatus::Converted, LeadStatus::Lost])
            ->latest()
            ->limit(10)
            ->get();

        return [
            'unassigned' => $unassigned,
            'stale' => $this->staleLeads->forTenant(),
        ];
    }
}
