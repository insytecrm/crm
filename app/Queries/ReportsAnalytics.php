<?php

namespace App\Queries;

use App\Enums\LeadScheduledEventStatus;
use App\Enums\LeadScheduledEventType;
use App\Enums\LeadStatus;
use App\Enums\PropertyType;
use App\Enums\TaskStatus;
use App\Models\Lead;
use App\Models\LeadScheduledEvent;
use App\Models\LeadTask;
use App\Models\Scopes\LeadVisibilityScope;
use App\Models\User;
use App\Support\DashboardPeriodFilter;
use App\Support\QueryableDate;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ReportsAnalytics
{
    private const TrendMonths = 12;

    /**
     * @return array{
     *     kpis: array{
     *         total_leads: int,
     *         converted_leads: int,
     *         active_leads: int,
     *         total_activities: int,
     *         todays_activities: int,
     *         lost_leads: int,
     *     },
     *     new_leads: list<array{key: string, label: string, count: int}>,
     *     activity_trend: list<array{key: string, label: string, count: int}>,
     *     by_status: list<array{key: string, label: string, count: int, percentage: float, color: string}>,
     *     by_property_type: list<array{key: string, label: string, count: int, percentage: float, color: string}>,
     *     by_user: list<array{key: string, label: string, count: int, percentage: float, color: string}>,
     *     agent_performance: list<array{key: string, agent: string, total_leads: int, active: int, converted: int, lost: int, conversion_ratio: float}>,
     *     site_visits: array{total: int, completion_rate: float, statuses: list<array{key: string, label: string, count: int, percentage: float, accent: string}>},
     *     follow_ups: array{total: int, completion_rate: float, statuses: list<array{key: string, label: string, count: int, percentage: float, accent: string}>},
     *     tasks: array{total: int, completion_rate: float, statuses: list<array{key: string, label: string, count: int, percentage: float, accent: string}>},
     * }
     */
    public function forTenant(DashboardPeriodFilter $filter): array
    {
        $statusCounts = $this->statusCounts($filter);
        $totalLeads = (int) $statusCounts->sum();
        $converted = (int) $statusCounts->get(LeadStatus::Converted->value, 0);
        $lost = (int) $statusCounts->get(LeadStatus::Lost->value, 0);
        $active = $totalLeads - $converted - $lost;

        return [
            'kpis' => [
                'total_leads' => $totalLeads,
                'converted_leads' => $converted,
                'active_leads' => max(0, $active),
                'total_activities' => $this->activityCount($filter),
                'todays_activities' => $this->todaysActivityCount($filter),
                'lost_leads' => $lost,
            ],
            'new_leads' => $this->leadTrend($filter),
            'activity_trend' => $this->activityTrend($filter),
            'by_status' => $this->leadsByStatus($statusCounts, $totalLeads),
            'by_property_type' => $this->leadsByPropertyType($filter),
            'by_user' => $this->leadsByUser($filter),
            'agent_performance' => $this->agentPerformance($filter),
            'site_visits' => $this->scheduledActivitySummary($filter, LeadScheduledEventType::SiteVisit),
            'follow_ups' => $this->scheduledActivitySummary($filter, LeadScheduledEventType::FollowUp),
            'tasks' => $this->taskSummary($filter),
        ];
    }

    private function activityCount(DashboardPeriodFilter $filter): int
    {
        [$from, $to] = $filter->dateBounds();

        $query = LeadScheduledEvent::query();
        QueryableDate::constrain($query, 'scheduled_at', $from, $to);

        return (int) $query->count();
    }

    private function todaysActivityCount(DashboardPeriodFilter $filter): int
    {
        [$from, $to] = $filter->dateBounds();
        $today = now()->toDateString();

        if ($from !== null && $today < $from) {
            return 0;
        }

        if ($to !== null && $today > $to) {
            return 0;
        }

        $query = LeadScheduledEvent::query();
        QueryableDate::constrainDay($query, 'scheduled_at', $today);

        return (int) $query->count();
    }

    /**
     * @return Collection<string, int>
     */
    private function statusCounts(DashboardPeriodFilter $filter): Collection
    {
        return $this->leadQueryForPeriod($filter, 'created_at')
            ->toBase()
            ->select('status', DB::raw('count(*) as aggregate'))
            ->groupBy('status')
            ->pluck('aggregate', 'status')
            ->map(fn (mixed $count): int => (int) $count);
    }

    /**
     * @return list<array{key: string, label: string, count: int}>
     */
    private function leadTrend(DashboardPeriodFilter $filter): array
    {
        [$from, $to, $bucket] = $this->trendWindow($filter);

        $grouped = QueryableDate::countsByBucket(
            $this->leadQuery()
                ->when($from !== null, fn (Builder $query): Builder => $query->where('created_at', '>=', $from))
                ->when($to !== null, fn (Builder $query): Builder => $query->where('created_at', '<=', $to)),
            'created_at',
            $bucket,
        );

        return $this->fillTrendPoints(Carbon::parse($from), Carbon::parse($to), $bucket, $grouped);
    }

    /**
     * @return list<array{key: string, label: string, count: int}>
     */
    private function activityTrend(DashboardPeriodFilter $filter): array
    {
        [$from, $to, $bucket] = $this->trendWindow($filter);

        $grouped = QueryableDate::countsByBucket(
            LeadScheduledEvent::query()
                ->when($from !== null, fn (Builder $query): Builder => $query->where('scheduled_at', '>=', $from))
                ->when($to !== null, fn (Builder $query): Builder => $query->where('scheduled_at', '<=', $to)),
            'scheduled_at',
            $bucket,
        );

        return $this->fillTrendPoints(Carbon::parse($from), Carbon::parse($to), $bucket, $grouped);
    }

    /**
     * @return array{0: string, 1: string, 2: 'day'|'month'}
     */
    private function trendWindow(DashboardPeriodFilter $filter): array
    {
        [$from, $to] = $filter->dateBounds();

        if ($from === null && $to === null) {
            $end = now()->copy()->endOfDay();
            $start = now()->copy()->subMonths(self::TrendMonths - 1)->startOfMonth();

            return [$start->toDateTimeString(), $end->toDateTimeString(), 'month'];
        }

        $start = Carbon::parse((string) $from)->startOfDay();
        $end = Carbon::parse((string) ($to ?? now()->toDateString()))->endOfDay();
        $bucket = $start->diffInDays($end) <= 45 ? 'day' : 'month';

        if ($bucket === 'month') {
            $start = $start->copy()->startOfMonth();
            $end = $end->copy()->endOfMonth();
        }

        return [$start->toDateTimeString(), $end->toDateTimeString(), $bucket];
    }

    /**
     * @param  Collection<string, int>  $grouped
     * @return list<array{key: string, label: string, count: int}>
     */
    private function fillTrendPoints(Carbon $start, Carbon $end, string $bucket, Collection $grouped): array
    {
        $points = [];
        $cursor = $bucket === 'day' ? $start->copy()->startOfDay() : $start->copy()->startOfMonth();
        $limit = $bucket === 'day' ? $end->copy()->startOfDay() : $end->copy()->startOfMonth();

        while ($cursor->lte($limit)) {
            $key = $cursor->format($bucket === 'day' ? 'Y-m-d' : 'Y-m');
            $points[] = [
                'key' => $key,
                'label' => $bucket === 'day' ? $cursor->format('j M') : $cursor->format('M'),
                'count' => (int) ($grouped[$key] ?? 0),
            ];

            if ($bucket === 'day') {
                $cursor->addDay();
            } else {
                $cursor->addMonth();
            }
        }

        return $points;
    }

    /**
     * @param  Collection<string, int>  $counts
     * @return list<array{key: string, label: string, count: int, percentage: float, color: string}>
     */
    private function leadsByStatus(Collection $counts, int $total): array
    {
        return collect(LeadStatus::cases())
            ->map(function (LeadStatus $status) use ($counts, $total): array {
                $count = $counts->get($status->value, 0);

                return [
                    'key' => $status->value,
                    'label' => $status->label(),
                    'count' => $count,
                    'percentage' => $total > 0 ? round(($count / $total) * 100, 1) : 0.0,
                    'color' => $this->statusColor($status),
                ];
            })
            ->filter(fn (array $row): bool => $row['count'] > 0)
            ->values()
            ->all();
    }

    /**
     * @return list<array{key: string, label: string, count: int, percentage: float, color: string}>
     */
    private function leadsByPropertyType(DashboardPeriodFilter $filter): array
    {
        $rows = $this->leadQueryForPeriod($filter, 'created_at')
            ->toBase()
            ->select('property_type', DB::raw('count(*) as aggregate'))
            ->groupBy('property_type')
            ->get();

        $total = (int) $rows->sum('aggregate');
        $palette = ['#5470C6', '#91CC75', '#FAC858', '#EE6666', '#73C0DE', '#3BA272', '#FC8452', '#9A60B4'];

        return $rows
            ->values()
            ->map(function (object $row, int $index) use ($total, $palette): array {
                $raw = $row->property_type !== null ? (string) $row->property_type : null;
                $type = $raw !== null ? PropertyType::tryFromMixed($raw) : null;
                $count = (int) $row->aggregate;

                return [
                    'key' => $raw ?? 'unknown',
                    'label' => $type?->label() ?? __('Unknown'),
                    'count' => $count,
                    'percentage' => $total > 0 ? round(($count / $total) * 100, 1) : 0.0,
                    'color' => $palette[$index % count($palette)],
                ];
            })
            ->sortByDesc('count')
            ->values()
            ->all();
    }

    /**
     * @return list<array{key: string, label: string, count: int, percentage: float, color: string}>
     */
    private function leadsByUser(DashboardPeriodFilter $filter): array
    {
        $rows = $this->leadQueryForPeriod($filter, 'created_at')
            ->toBase()
            ->select('assigned_to_id', DB::raw('count(*) as aggregate'))
            ->groupBy('assigned_to_id')
            ->get();

        $total = (int) $rows->sum('aggregate');
        $userIds = $rows->pluck('assigned_to_id')->filter()->map(fn (mixed $id): int => (int) $id)->all();
        $names = User::query()
            ->whereIn('id', $userIds)
            ->pluck('name', 'id');

        $palette = ['#5470C6', '#91CC75', '#FAC858', '#EE6666', '#73C0DE', '#3BA272', '#FC8452', '#9A60B4'];

        return $rows
            ->values()
            ->map(function (object $row, int $index) use ($names, $total, $palette): array {
                $id = $row->assigned_to_id !== null ? (int) $row->assigned_to_id : null;
                $count = (int) $row->aggregate;

                return [
                    'key' => $id !== null ? (string) $id : 'unassigned',
                    'label' => $id !== null ? ($names[$id] ?? __('Unknown')) : __('Unassigned'),
                    'count' => $count,
                    'percentage' => $total > 0 ? round(($count / $total) * 100, 1) : 0.0,
                    'color' => $palette[$index % count($palette)],
                ];
            })
            ->sortByDesc('count')
            ->values()
            ->all();
    }

    /**
     * @return list<array{key: string, agent: string, total_leads: int, active: int, converted: int, lost: int, conversion_ratio: float}>
     */
    private function agentPerformance(DashboardPeriodFilter $filter): array
    {
        $convertedStatus = LeadStatus::Converted->value;
        $lostStatus = LeadStatus::Lost->value;

        $rows = $this->leadQueryForPeriod($filter, 'created_at')
            ->toBase()
            ->select(
                'assigned_to_id',
                DB::raw('count(*) as total_leads'),
                DB::raw("sum(case when status = '{$convertedStatus}' then 1 else 0 end) as converted"),
                DB::raw("sum(case when status = '{$lostStatus}' then 1 else 0 end) as lost"),
            )
            ->groupBy('assigned_to_id')
            ->get();

        $userIds = $rows->pluck('assigned_to_id')->filter()->map(fn (mixed $id): int => (int) $id)->all();
        $names = User::query()
            ->whereIn('id', $userIds)
            ->pluck('name', 'id');

        return $rows
            ->map(function (object $row) use ($names): array {
                $id = $row->assigned_to_id !== null ? (int) $row->assigned_to_id : null;
                $total = (int) $row->total_leads;
                $converted = (int) $row->converted;
                $lost = (int) $row->lost;
                $active = max(0, $total - $converted - $lost);

                return [
                    'key' => $id !== null ? (string) $id : 'unassigned',
                    'agent' => $id !== null ? ($names[$id] ?? __('Unknown')) : __('Unassigned'),
                    'total_leads' => $total,
                    'active' => $active,
                    'converted' => $converted,
                    'lost' => $lost,
                    'conversion_ratio' => $total > 0 ? round(($converted / $total) * 100, 1) : 0.0,
                ];
            })
            ->sortByDesc('total_leads')
            ->values()
            ->all();
    }

    /**
     * @return array{total: int, completion_rate: float, statuses: list<array{key: string, label: string, count: int, percentage: float, accent: string}>}
     */
    private function scheduledActivitySummary(DashboardPeriodFilter $filter, LeadScheduledEventType $type): array
    {
        [$from, $to] = $filter->dateBounds();
        $now = now()->toDateTimeString();
        $scheduledStatus = LeadScheduledEventStatus::Scheduled->value;
        $completedStatus = LeadScheduledEventStatus::Completed->value;

        $query = LeadScheduledEvent::query()->where('type', $type);
        QueryableDate::constrain($query, 'scheduled_at', $from, $to);

        $row = $query
            ->toBase()
            ->selectRaw(
                'coalesce(sum(case when status = ? and scheduled_at >= ? then 1 else 0 end), 0) as scheduled_count,
                coalesce(sum(case when status = ? and scheduled_at < ? then 1 else 0 end), 0) as overdue_count,
                coalesce(sum(case when status = ? then 1 else 0 end), 0) as completed_count',
                [$scheduledStatus, $now, $scheduledStatus, $now, $completedStatus],
            )
            ->first();

        $scheduled = (int) ($row->scheduled_count ?? 0);
        $overdue = (int) ($row->overdue_count ?? 0);
        $completed = (int) ($row->completed_count ?? 0);

        return $this->statusSummary([
            ['key' => 'scheduled', 'label' => __('Scheduled'), 'count' => $scheduled, 'accent' => 'sky'],
            ['key' => 'overdue', 'label' => __('Overdue'), 'count' => $overdue, 'accent' => 'amber'],
            ['key' => 'completed', 'label' => __('Completed'), 'count' => $completed, 'accent' => 'emerald'],
        ]);
    }

    /**
     * @return array{total: int, completion_rate: float, statuses: list<array{key: string, label: string, count: int, percentage: float, accent: string}>}
     */
    private function taskSummary(DashboardPeriodFilter $filter): array
    {
        [$from, $to] = $filter->dateBounds();

        $query = LeadTask::query()->whereIn('status', [
            TaskStatus::Pending->value,
            TaskStatus::InProgress->value,
            TaskStatus::Complete->value,
        ]);
        QueryableDate::constrain($query, 'created_at', $from, $to);

        $pendingStatus = TaskStatus::Pending->value;
        $inProgressStatus = TaskStatus::InProgress->value;
        $completeStatus = TaskStatus::Complete->value;

        $row = $query
            ->toBase()
            ->selectRaw(
                'coalesce(sum(case when status = ? then 1 else 0 end), 0) as pending_count,
                coalesce(sum(case when status = ? then 1 else 0 end), 0) as in_progress_count,
                coalesce(sum(case when status = ? then 1 else 0 end), 0) as completed_count',
                [$pendingStatus, $inProgressStatus, $completeStatus],
            )
            ->first();

        $pending = (int) ($row->pending_count ?? 0);
        $inProgress = (int) ($row->in_progress_count ?? 0);
        $completed = (int) ($row->completed_count ?? 0);

        return $this->statusSummary([
            ['key' => 'pending', 'label' => __('Pending'), 'count' => $pending, 'accent' => 'slate'],
            ['key' => 'in_progress', 'label' => __('In Progress'), 'count' => $inProgress, 'accent' => 'sky'],
            ['key' => 'completed', 'label' => __('Completed'), 'count' => $completed, 'accent' => 'emerald'],
        ]);
    }

    /**
     * @param  list<array{key: string, label: string, count: int, accent: string}>  $statuses
     * @return array{total: int, completion_rate: float, statuses: list<array{key: string, label: string, count: int, percentage: float, accent: string}>}
     */
    private function statusSummary(array $statuses): array
    {
        $total = (int) collect($statuses)->sum('count');
        $completed = (int) (collect($statuses)->firstWhere('key', 'completed')['count'] ?? 0);

        return [
            'total' => $total,
            'completion_rate' => $total > 0 ? round(($completed / $total) * 100, 1) : 0.0,
            'statuses' => collect($statuses)
                ->map(fn (array $status): array => [
                    'key' => $status['key'],
                    'label' => $status['label'],
                    'count' => $status['count'],
                    'percentage' => $total > 0 ? round(($status['count'] / $total) * 100, 1) : 0.0,
                    'accent' => $status['accent'],
                ])
                ->all(),
        ];
    }

    /**
     * @return Builder<Lead>
     */
    private function leadQueryForPeriod(DashboardPeriodFilter $filter, string $column): Builder
    {
        [$from, $to] = $filter->dateBounds();

        $query = $this->leadQuery();
        QueryableDate::constrain($query, $column, $from, $to);

        return $query;
    }

    /**
     * @return Builder<Lead>
     */
    private function leadQuery(): Builder
    {
        return Lead::query()->withoutGlobalScope(LeadVisibilityScope::class);
    }

    private function statusColor(LeadStatus $status): string
    {
        return match ($status) {
            LeadStatus::New => '#73C0DE',
            LeadStatus::Contacted => '#5470C6',
            LeadStatus::Qualified => '#9A60B4',
            LeadStatus::FollowUp => '#FAC858',
            LeadStatus::SiteVisit => '#FC8452',
            LeadStatus::Negotiation => '#EA7CCC',
            LeadStatus::Converted => '#91CC75',
            LeadStatus::Lost => '#EE6666',
        };
    }
}
