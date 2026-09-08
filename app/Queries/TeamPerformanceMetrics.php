<?php

namespace App\Queries;

use App\Enums\LeadScheduledEventStatus;
use App\Enums\LeadScheduledEventType;
use App\Enums\LeadStatus;
use App\Enums\TaskStatus;
use App\Models\Lead;
use App\Models\LeadScheduledEvent;
use App\Models\LeadTask;
use App\Models\SalesTeam;
use App\Models\Scopes\LeadVisibilityScope;
use App\Support\DashboardPeriodFilter;
use App\Support\QueryableDate;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class TeamPerformanceMetrics
{
    /**
     * @return list<array{
     *     id: int,
     *     name: string,
     *     subtitle: string,
     *     href: string,
     *     rank: int,
     *     total_leads: int,
     *     active: int,
     *     converted: int,
     *     lost: int,
     *     conversion_rate: float,
     *     follow_up_rate: float,
     *     site_visit_rate: float,
     *     task_rate: float,
     * }>
     */
    public function forTeams(DashboardPeriodFilter $filter): array
    {
        $teams = SalesTeam::query()
            ->with(['manager:id,name', 'members:id,name'])
            ->withCount('members')
            ->orderBy('name')
            ->get();

        if ($teams->isEmpty()) {
            return [];
        }

        $memberIdsByTeam = $teams->mapWithKeys(fn (SalesTeam $team): array => [
            $team->id => $this->teamUserIds($team),
        ]);

        $allUserIds = $memberIdsByTeam->flatten()->unique()->values()->all();
        $metricsByUser = $this->metricsByUserId($allUserIds, $filter);

        $cards = $teams
            ->map(function (SalesTeam $team) use ($memberIdsByTeam, $metricsByUser): array {
                $userIds = $memberIdsByTeam->get($team->id, []);
                $aggregated = $this->aggregateMetrics(
                    collect($userIds)->map(
                        fn (int $userId): array => $metricsByUser[$userId] ?? $this->emptyUserMetrics()
                    )
                );

                $managerName = $team->manager?->name;
                $memberCount = (int) $team->members_count;
                $subtitleParts = array_filter([
                    $managerName,
                    trans_choice(':count member|:count members', $memberCount, ['count' => $memberCount]),
                ]);

                return [
                    'id' => $team->id,
                    'name' => $team->name,
                    'subtitle' => implode(' · ', $subtitleParts),
                    'href' => route('tenant.teams.performance.show', $team),
                    'rank' => 0,
                    ...$this->publicMetrics($aggregated),
                ];
            })
            ->all();

        return $this->withRanks($cards);
    }

    /**
     * @return list<array{
     *     id: int,
     *     name: string,
     *     subtitle: string,
     *     href: null,
     *     rank: int,
     *     total_leads: int,
     *     active: int,
     *     converted: int,
     *     lost: int,
     *     conversion_rate: float,
     *     follow_up_rate: float,
     *     site_visit_rate: float,
     *     task_rate: float,
     * }>
     */
    public function forTeamMembers(SalesTeam $team, DashboardPeriodFilter $filter): array
    {
        $team->loadMissing(['members.role', 'manager.role']);

        $users = collect()
            ->when($team->manager !== null, fn (Collection $collection): Collection => $collection->push($team->manager))
            ->merge($team->members)
            ->unique('id')
            ->sortBy('name')
            ->values();

        if ($users->isEmpty()) {
            return [];
        }

        $userIds = $users->pluck('id')->map(fn (mixed $id): int => (int) $id)->all();
        $metricsByUser = $this->metricsByUserId($userIds, $filter);

        $cards = $users
            ->map(function ($user) use ($metricsByUser, $team): array {
                $metrics = $metricsByUser[(int) $user->id] ?? $this->emptyUserMetrics();
                $isManager = $team->manager_id !== null && (int) $user->id === (int) $team->manager_id;
                $roleLabel = $user->role?->name;

                if ($isManager && filled($roleLabel)) {
                    $subtitle = $roleLabel.' · '.__('Manager');
                } elseif ($isManager) {
                    $subtitle = __('Manager');
                } else {
                    $subtitle = $roleLabel ?? '';
                }

                return [
                    'id' => (int) $user->id,
                    'name' => $user->name,
                    'subtitle' => $subtitle,
                    'href' => null,
                    'rank' => 0,
                    ...$this->publicMetrics($metrics),
                ];
            })
            ->all();

        return $this->withRanks($cards);
    }

    /**
     * @param  list<int>  $userIds
     * @return array<int, array{
     *     total_leads: int,
     *     active: int,
     *     converted: int,
     *     lost: int,
     *     follow_ups_total: int,
     *     follow_ups_completed: int,
     *     site_visits_total: int,
     *     site_visits_completed: int,
     *     tasks_total: int,
     *     tasks_completed: int,
     * }>
     */
    private function metricsByUserId(array $userIds, DashboardPeriodFilter $filter): array
    {
        if ($userIds === []) {
            return [];
        }

        $leadStats = $this->leadStatsByUser($userIds, $filter);
        $followUps = $this->activityRatesByUser($userIds, $filter, LeadScheduledEventType::FollowUp);
        $siteVisits = $this->activityRatesByUser($userIds, $filter, LeadScheduledEventType::SiteVisit);
        $tasks = $this->taskRatesByUser($userIds, $filter);

        $metrics = [];

        foreach ($userIds as $userId) {
            $leads = $leadStats[$userId] ?? ['total' => 0, 'converted' => 0, 'lost' => 0];
            $followUp = $followUps[$userId] ?? ['total' => 0, 'completed' => 0];
            $siteVisit = $siteVisits[$userId] ?? ['total' => 0, 'completed' => 0];
            $task = $tasks[$userId] ?? ['total' => 0, 'completed' => 0];
            $total = $leads['total'];
            $converted = $leads['converted'];
            $lost = $leads['lost'];

            $metrics[$userId] = [
                'total_leads' => $total,
                'active' => max(0, $total - $converted - $lost),
                'converted' => $converted,
                'lost' => $lost,
                'follow_ups_total' => $followUp['total'],
                'follow_ups_completed' => $followUp['completed'],
                'site_visits_total' => $siteVisit['total'],
                'site_visits_completed' => $siteVisit['completed'],
                'tasks_total' => $task['total'],
                'tasks_completed' => $task['completed'],
            ];
        }

        return $metrics;
    }

    /**
     * @param  list<int>  $userIds
     * @return array<int, array{total: int, converted: int, lost: int}>
     */
    private function leadStatsByUser(array $userIds, DashboardPeriodFilter $filter): array
    {
        $convertedStatus = LeadStatus::Converted->value;
        $lostStatus = LeadStatus::Lost->value;

        $query = Lead::query()
            ->withoutGlobalScope(LeadVisibilityScope::class)
            ->whereIn('assigned_to_id', $userIds);

        QueryableDate::constrain($query, 'created_at', ...$filter->dateBounds());

        return $query
            ->toBase()
            ->select(
                'assigned_to_id',
                DB::raw('count(*) as total'),
                DB::raw("sum(case when status = '{$convertedStatus}' then 1 else 0 end) as converted"),
                DB::raw("sum(case when status = '{$lostStatus}' then 1 else 0 end) as lost"),
            )
            ->groupBy('assigned_to_id')
            ->get()
            ->mapWithKeys(fn (object $row): array => [
                (int) $row->assigned_to_id => [
                    'total' => (int) $row->total,
                    'converted' => (int) $row->converted,
                    'lost' => (int) $row->lost,
                ],
            ])
            ->all();
    }

    /**
     * @param  list<int>  $userIds
     * @return array<int, array{total: int, completed: int}>
     */
    private function activityRatesByUser(array $userIds, DashboardPeriodFilter $filter, LeadScheduledEventType $type): array
    {
        $completedStatus = LeadScheduledEventStatus::Completed->value;

        $query = LeadScheduledEvent::query()
            ->where('type', $type)
            ->whereIn('user_id', $userIds);

        QueryableDate::constrain($query, 'scheduled_at', ...$filter->dateBounds());

        return $query
            ->toBase()
            ->select(
                'user_id',
                DB::raw('count(*) as total'),
                DB::raw("sum(case when status = '{$completedStatus}' then 1 else 0 end) as completed"),
            )
            ->groupBy('user_id')
            ->get()
            ->mapWithKeys(fn (object $row): array => [
                (int) $row->user_id => [
                    'total' => (int) $row->total,
                    'completed' => (int) $row->completed,
                ],
            ])
            ->all();
    }

    /**
     * @param  list<int>  $userIds
     * @return array<int, array{total: int, completed: int}>
     */
    private function taskRatesByUser(array $userIds, DashboardPeriodFilter $filter): array
    {
        $completeStatus = TaskStatus::Complete->value;

        $query = LeadTask::query()
            ->whereIn('assigned_to_id', $userIds)
            ->whereIn('status', [
                TaskStatus::Pending->value,
                TaskStatus::InProgress->value,
                TaskStatus::Complete->value,
            ]);

        QueryableDate::constrain($query, 'created_at', ...$filter->dateBounds());

        return $query
            ->toBase()
            ->select(
                'assigned_to_id',
                DB::raw('count(*) as total'),
                DB::raw("sum(case when status = '{$completeStatus}' then 1 else 0 end) as completed"),
            )
            ->groupBy('assigned_to_id')
            ->get()
            ->mapWithKeys(fn (object $row): array => [
                (int) $row->assigned_to_id => [
                    'total' => (int) $row->total,
                    'completed' => (int) $row->completed,
                ],
            ])
            ->all();
    }

    /**
     * @param  Collection<int, array{
     *     total_leads: int,
     *     active: int,
     *     converted: int,
     *     lost: int,
     *     follow_ups_total: int,
     *     follow_ups_completed: int,
     *     site_visits_total: int,
     *     site_visits_completed: int,
     *     tasks_total: int,
     *     tasks_completed: int,
     * }>  $rows
     * @return array{
     *     total_leads: int,
     *     active: int,
     *     converted: int,
     *     lost: int,
     *     follow_ups_total: int,
     *     follow_ups_completed: int,
     *     site_visits_total: int,
     *     site_visits_completed: int,
     *     tasks_total: int,
     *     tasks_completed: int,
     * }
     */
    private function aggregateMetrics(Collection $rows): array
    {
        return [
            'total_leads' => (int) $rows->sum('total_leads'),
            'active' => (int) $rows->sum('active'),
            'converted' => (int) $rows->sum('converted'),
            'lost' => (int) $rows->sum('lost'),
            'follow_ups_total' => (int) $rows->sum('follow_ups_total'),
            'follow_ups_completed' => (int) $rows->sum('follow_ups_completed'),
            'site_visits_total' => (int) $rows->sum('site_visits_total'),
            'site_visits_completed' => (int) $rows->sum('site_visits_completed'),
            'tasks_total' => (int) $rows->sum('tasks_total'),
            'tasks_completed' => (int) $rows->sum('tasks_completed'),
        ];
    }

    /**
     * @param  array{
     *     total_leads: int,
     *     active: int,
     *     converted: int,
     *     lost: int,
     *     follow_ups_total: int,
     *     follow_ups_completed: int,
     *     site_visits_total: int,
     *     site_visits_completed: int,
     *     tasks_total: int,
     *     tasks_completed: int,
     * }  $metrics
     * @return array{
     *     total_leads: int,
     *     active: int,
     *     converted: int,
     *     lost: int,
     *     conversion_rate: float,
     *     follow_up_rate: float,
     *     site_visit_rate: float,
     *     task_rate: float,
     * }
     */
    private function publicMetrics(array $metrics): array
    {
        return [
            'total_leads' => $metrics['total_leads'],
            'active' => $metrics['active'],
            'converted' => $metrics['converted'],
            'lost' => $metrics['lost'],
            'conversion_rate' => $this->percentage($metrics['converted'], $metrics['total_leads']),
            'follow_up_rate' => $this->percentage($metrics['follow_ups_completed'], $metrics['follow_ups_total']),
            'site_visit_rate' => $this->percentage($metrics['site_visits_completed'], $metrics['site_visits_total']),
            'task_rate' => $this->percentage($metrics['tasks_completed'], $metrics['tasks_total']),
        ];
    }

    /**
     * @return array{
     *     total_leads: int,
     *     active: int,
     *     converted: int,
     *     lost: int,
     *     follow_ups_total: int,
     *     follow_ups_completed: int,
     *     site_visits_total: int,
     *     site_visits_completed: int,
     *     tasks_total: int,
     *     tasks_completed: int,
     * }
     */
    private function emptyUserMetrics(): array
    {
        return [
            'total_leads' => 0,
            'active' => 0,
            'converted' => 0,
            'lost' => 0,
            'follow_ups_total' => 0,
            'follow_ups_completed' => 0,
            'site_visits_total' => 0,
            'site_visits_completed' => 0,
            'tasks_total' => 0,
            'tasks_completed' => 0,
        ];
    }

    /**
     * @return list<int>
     */
    private function teamUserIds(SalesTeam $team): array
    {
        $ids = $team->members->pluck('id');

        if ($team->manager_id !== null) {
            $ids->push($team->manager_id);
        }

        return $ids
            ->unique()
            ->values()
            ->map(fn (mixed $id): int => (int) $id)
            ->all();
    }

    /**
     * @param  list<array<string, mixed>>  $cards
     * @return list<array<string, mixed>>
     */
    private function withRanks(array $cards): array
    {
        return collect($cards)
            ->sort(function (array $left, array $right): int {
                return [$right['conversion_rate'], $right['total_leads'], $left['name']]
                    <=> [$left['conversion_rate'], $left['total_leads'], $right['name']];
            })
            ->values()
            ->map(function (array $card, int $index): array {
                $card['rank'] = $index + 1;

                return $card;
            })
            ->all();
    }

    private function percentage(int $part, int $total): float
    {
        return $total > 0 ? round(($part / $total) * 100, 1) : 0.0;
    }
}
