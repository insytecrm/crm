<?php

namespace App\Queries;

use App\Enums\LeadScheduledEventStatus;
use App\Enums\LeadScheduledEventType;
use App\Enums\LeadSource;
use App\Enums\LeadStatus;
use App\Enums\PropertyType;
use App\Enums\ScheduledActivityOutcome;
use App\Enums\SiteVisitOutcome;
use App\Models\Lead;
use App\Models\LeadScheduledEvent;
use App\Models\SalesTeam;
use App\Models\Scopes\LeadVisibilityScope;
use App\Support\DashboardPeriodFilter;
use App\Support\QueryableDate;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class AnalyticsMetrics
{
    private const TrendMonths = 12;

    private const TopLeadsLimit = 10;

    /** @var list<string> */
    private const OutcomePalette = ['#5470C6', '#91CC75', '#FAC858', '#EE6666', '#73C0DE', '#3BA272', '#FC8452', '#9A60B4'];

    /**
     * @return array{
     *     kpis: array{
     *         leads_per_day: array{value: float, display: string},
     *         conversion_rate: array{value: float, display: string},
     *         avg_response_time: array{value: ?int, display: string},
     *         engagement: array{value: float, display: string},
     *         follow_up_rate: array{value: float, scheduled: int, completed: int, display: string},
     *         site_visit_rate: array{value: float, scheduled: int, completed: int, display: string},
     *     },
     *     lead_velocity: list<array{key: string, label: string, count: int, cumulative: int}>,
     *     lead_aging: list<array{key: string, label: string, count: int, percentage: float, color: string}>,
     *     property_type_performance: list<array{key: string, property_type: string, total: int, active: int, converted: int, lost: int, conversion_rate: float, status: string, status_label: string}>,
     *     source_performance: list<array{key: string, source: string, total: int, active: int, converted: int, lost: int, conversion_rate: float}>,
     *     top_leads: EloquentCollection<int, Lead>,
     *     site_visit_outcomes: array{total: int, points: list<array{key: string, label: string, count: int, color: string}>},
     *     follow_up_outcomes: array{total: int, points: list<array{key: string, label: string, count: int, color: string}>},
     * }
     */
    public function forTenant(DashboardPeriodFilter $filter): array
    {
        $leadStats = $this->leadStats($filter);
        $followUp = $this->completionRate($filter, LeadScheduledEventType::FollowUp);
        $siteVisit = $this->completionRate($filter, LeadScheduledEventType::SiteVisit);

        return [
            'kpis' => [
                'leads_per_day' => $this->leadsPerDay($leadStats['total'], $filter),
                'conversion_rate' => $this->percentageMetric($leadStats['converted'], $leadStats['total']),
                'avg_response_time' => $this->avgResponseTime($filter),
                'engagement' => $this->percentageMetric($leadStats['engaged'], $leadStats['total']),
                'follow_up_rate' => $this->rateMetric($followUp['completed'], $followUp['total']),
                'site_visit_rate' => $this->rateMetric($siteVisit['completed'], $siteVisit['total']),
            ],
            'lead_velocity' => $this->leadVelocityTrend($filter),
            'lead_aging' => $this->leadAging($filter),
            'property_type_performance' => $this->propertyTypePerformance($filter),
            'source_performance' => $this->sourcePerformance($filter),
            'top_leads' => $this->topLeads($filter),
            'site_visit_outcomes' => $this->outcomeSummary(
                $filter,
                LeadScheduledEventType::SiteVisit,
                SiteVisitOutcome::options(),
            ),
            'follow_up_outcomes' => $this->outcomeSummary(
                $filter,
                LeadScheduledEventType::FollowUp,
                ScheduledActivityOutcome::options(),
            ),
        ];
    }

    /**
     * @return array{total: int, converted: int, engaged: int}
     */
    private function leadStats(DashboardPeriodFilter $filter): array
    {
        $row = $this->leadQueryForPeriod($filter, 'created_at')
            ->toBase()
            ->selectRaw(
                'count(*) as total,
                sum(case when status = ? then 1 else 0 end) as converted,
                sum(case when status != ? then 1 else 0 end) as engaged',
                [LeadStatus::Converted->value, LeadStatus::New->value],
            )
            ->first();

        return [
            'total' => (int) ($row->total ?? 0),
            'converted' => (int) ($row->converted ?? 0),
            'engaged' => (int) ($row->engaged ?? 0),
        ];
    }

    /**
     * @return array{value: float, display: string}
     */
    private function leadsPerDay(int $totalLeads, DashboardPeriodFilter $filter): array
    {
        $days = $this->periodDayCount($filter);
        $value = $days > 0 ? round($totalLeads / $days, 2) : 0.0;
        $formatted = rtrim(rtrim(number_format($value, 2, '.', ''), '0'), '.');

        return [
            'value' => $value,
            'display' => ($formatted === '' ? '0' : $formatted).'/day',
        ];
    }

    /**
     * @return array{value: float, display: string}
     */
    private function percentageMetric(int $part, int $total): array
    {
        $value = $total > 0 ? round(($part / $total) * 100, 1) : 0.0;

        return [
            'value' => $value,
            'display' => number_format($value, $value == floor($value) ? 0 : 1).'%',
        ];
    }

    /**
     * @return array{value: float, scheduled: int, completed: int, display: string}
     */
    private function rateMetric(int $completed, int $total): array
    {
        $value = $total > 0 ? round(($completed / $total) * 100, 1) : 0.0;
        $percent = number_format($value, $value == floor($value) ? 0 : 1).'%';

        return [
            'value' => $value,
            'scheduled' => $total,
            'completed' => $completed,
            'display' => $total > 0
                ? number_format($completed).'/'.number_format($total).' · '.$percent
                : '0/0 · 0%',
        ];
    }

    /**
     * @return array{value: ?int, display: string}
     */
    private function avgResponseTime(DashboardPeriodFilter $filter): array
    {
        [$from, $to] = $filter->dateBounds();

        $query = DB::table('lead_scheduled_events as events')
            ->join('leads', 'leads.id', '=', 'events.lead_id')
            ->where('events.type', LeadScheduledEventType::FollowUp->value)
            ->where('events.sequence_number', 1)
            ->where('events.status', LeadScheduledEventStatus::Completed->value)
            ->whereNotNull('events.completed_at');

        $this->constrainAssignees($query, $filter, 'leads.assigned_to_id');
        QueryableDate::constrain($query, 'leads.created_at', $from, $to);

        $seconds = QueryableDate::averagePositiveDiffInSeconds(
            $query,
            'leads.created_at',
            'events.completed_at',
        );

        if ($seconds === null) {
            return [
                'value' => null,
                'display' => '—',
            ];
        }

        return [
            'value' => $seconds,
            'display' => $this->formatDuration($seconds),
        ];
    }

    /**
     * @return array{total: int, completed: int}
     */
    private function completionRate(DashboardPeriodFilter $filter, LeadScheduledEventType $type): array
    {
        [$from, $to] = $filter->dateBounds();

        $events = LeadScheduledEvent::query()->where('type', $type);
        $this->constrainEventAssignees($events, $filter);
        QueryableDate::constrain($events, 'scheduled_at', $from, $to);

        $row = $events
            ->toBase()
            ->selectRaw(
                'count(*) as total, coalesce(sum(case when status = ? then 1 else 0 end), 0) as completed',
                [LeadScheduledEventStatus::Completed->value],
            )
            ->first();

        return [
            'total' => (int) ($row->total ?? 0),
            'completed' => (int) ($row->completed ?? 0),
        ];
    }

    /**
     * @return list<array{key: string, label: string, count: int, cumulative: int}>
     */
    private function leadVelocityTrend(DashboardPeriodFilter $filter): array
    {
        [$from, $to, $bucket] = $this->trendWindow($filter);

        $grouped = QueryableDate::countsByBucket(
            $this->leadQuery($filter)
                ->when($from !== null, fn (Builder $query): Builder => $query->where('created_at', '>=', $from))
                ->when($to !== null, fn (Builder $query): Builder => $query->where('created_at', '<=', $to)),
            'created_at',
            $bucket,
        );

        $points = [];
        $cumulative = 0;
        $cursor = $bucket === 'day'
            ? Carbon::parse($from)->startOfDay()
            : Carbon::parse($from)->startOfMonth();
        $limit = $bucket === 'day'
            ? Carbon::parse($to)->startOfDay()
            : Carbon::parse($to)->startOfMonth();

        while ($cursor->lte($limit)) {
            $key = $cursor->format($bucket === 'day' ? 'Y-m-d' : 'Y-m');
            $count = (int) ($grouped[$key] ?? 0);
            $cumulative += $count;

            $points[] = [
                'key' => $key,
                'label' => $bucket === 'day' ? $cursor->format('j M') : $cursor->format('M'),
                'count' => $count,
                'cumulative' => $cumulative,
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
     * @return list<array{key: string, label: string, count: int, percentage: float, color: string}>
     */
    private function leadAging(DashboardPeriodFilter $filter): array
    {
        $buckets = [
            '0_7' => ['label' => __('0–7 days'), 'color' => '#73C0DE', 'count' => 0],
            '8_14' => ['label' => __('8–14 days'), 'color' => '#5470C6', 'count' => 0],
            '15_30' => ['label' => __('15–30 days'), 'color' => '#FAC858', 'count' => 0],
            '31_60' => ['label' => __('31–60 days'), 'color' => '#FC8452', 'count' => 0],
            '60_plus' => ['label' => __('60+ days'), 'color' => '#EE6666', 'count' => 0],
        ];

        $counts = QueryableDate::agingBucketCounts(
            $this->leadQueryForPeriod($filter, 'created_at')
                ->whereNotIn('status', [LeadStatus::Converted->value, LeadStatus::Lost->value]),
            'created_at',
        );

        foreach ($counts as $key => $count) {
            $buckets[$key]['count'] = $count;
        }

        $total = (int) collect($buckets)->sum('count');

        return collect($buckets)
            ->map(fn (array $bucket, string $key): array => [
                'key' => $key,
                'label' => $bucket['label'],
                'count' => $bucket['count'],
                'percentage' => $total > 0 ? round(($bucket['count'] / $total) * 100, 1) : 0.0,
                'color' => $bucket['color'],
            ])
            ->filter(fn (array $row): bool => $row['count'] > 0)
            ->values()
            ->all();
    }

    /**
     * @return list<array{key: string, property_type: string, total: int, active: int, converted: int, lost: int, conversion_rate: float, status: string, status_label: string}>
     */
    private function propertyTypePerformance(DashboardPeriodFilter $filter): array
    {
        $convertedStatus = LeadStatus::Converted->value;
        $lostStatus = LeadStatus::Lost->value;

        $rows = $this->leadQueryForPeriod($filter, 'created_at')
            ->toBase()
            ->select(
                'property_type',
                DB::raw('count(*) as total'),
                DB::raw("sum(case when status = '{$convertedStatus}' then 1 else 0 end) as converted"),
                DB::raw("sum(case when status = '{$lostStatus}' then 1 else 0 end) as lost"),
            )
            ->groupBy('property_type')
            ->get()
            ->keyBy(fn (object $row): string => $row->property_type !== null ? (string) $row->property_type : 'unknown');

        return collect(PropertyType::cases())
            ->map(function (PropertyType $type) use ($rows): array {
                $row = $rows->get($type->value);
                $total = (int) ($row->total ?? 0);
                $converted = (int) ($row->converted ?? 0);
                $lost = (int) ($row->lost ?? 0);
                $active = max(0, $total - $converted - $lost);
                $conversionRate = $total > 0 ? round(($converted / $total) * 100, 1) : 0.0;
                $status = $this->conversionPerformanceStatus($conversionRate);

                return [
                    'key' => $type->value,
                    'property_type' => $type->label(),
                    'total' => $total,
                    'active' => $active,
                    'converted' => $converted,
                    'lost' => $lost,
                    'conversion_rate' => $conversionRate,
                    'status' => $status,
                    'status_label' => match ($status) {
                        'need_work' => __('Need Work'),
                        'average' => __('Average'),
                        'good' => __('Good'),
                        'excellent' => __('Excellent'),
                    },
                ];
            })
            ->sortByDesc('total')
            ->values()
            ->all();
    }

    /**
     * @return list<array{key: string, source: string, total: int, active: int, converted: int, lost: int, conversion_rate: float}>
     */
    private function sourcePerformance(DashboardPeriodFilter $filter): array
    {
        $convertedStatus = LeadStatus::Converted->value;
        $lostStatus = LeadStatus::Lost->value;

        $rows = $this->leadQueryForPeriod($filter, 'created_at')
            ->toBase()
            ->select(
                'source',
                DB::raw('count(*) as total'),
                DB::raw("sum(case when status = '{$convertedStatus}' then 1 else 0 end) as converted"),
                DB::raw("sum(case when status = '{$lostStatus}' then 1 else 0 end) as lost"),
            )
            ->groupBy('source')
            ->get();

        return $rows
            ->map(function (object $row): array {
                $raw = filled($row->source) ? trim((string) $row->source) : null;
                $total = (int) $row->total;
                $converted = (int) $row->converted;
                $lost = (int) $row->lost;
                $active = max(0, $total - $converted - $lost);
                $sourceLabel = $raw !== null
                    ? (LeadSource::tryFromMixed($raw)?->label() ?? $raw)
                    : __('Unknown');

                return [
                    'key' => $raw ?? 'unknown',
                    'source' => $sourceLabel,
                    'total' => $total,
                    'active' => $active,
                    'converted' => $converted,
                    'lost' => $lost,
                    'conversion_rate' => $total > 0 ? round(($converted / $total) * 100, 1) : 0.0,
                ];
            })
            ->sortByDesc('total')
            ->values()
            ->all();
    }

    /**
     * @return EloquentCollection<int, Lead>
     */
    private function topLeads(DashboardPeriodFilter $filter): EloquentCollection
    {
        return $this->leadQueryForPeriod($filter, 'created_at')
            ->with(['assignedTo:id,name'])
            ->orderByDesc('lead_score')
            ->orderByDesc('id')
            ->limit(self::TopLeadsLimit)
            ->get();
    }

    private function conversionPerformanceStatus(float $conversionRate): string
    {
        return match (true) {
            $conversionRate < 3.0 => 'need_work',
            $conversionRate <= 5.0 => 'average',
            $conversionRate <= 15.0 => 'good',
            default => 'excellent',
        };
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

    private function periodDayCount(DashboardPeriodFilter $filter): int
    {
        [$from, $to] = $filter->dateBounds();

        if ($from === null && $to === null) {
            $earliest = $this->leadQuery($filter)->toBase()->min('created_at');

            if ($earliest === null) {
                return 1;
            }

            $from = Carbon::parse((string) $earliest)->toDateString();
            $to = now()->toDateString();
        }

        $start = Carbon::parse((string) $from)->startOfDay();
        $end = Carbon::parse((string) ($to ?? now()->toDateString()))->startOfDay();

        return max(1, $start->diffInDays($end) + 1);
    }

    private function formatDuration(int $seconds): string
    {
        if ($seconds < 60) {
            return $seconds.'s';
        }

        if ($seconds < 3600) {
            return (int) round($seconds / 60).'m';
        }

        if ($seconds < 86400) {
            $hours = intdiv($seconds, 3600);
            $minutes = intdiv($seconds % 3600, 60);

            return $minutes > 0 ? "{$hours}h {$minutes}m" : "{$hours}h";
        }

        $days = intdiv($seconds, 86400);
        $hours = intdiv($seconds % 86400, 3600);

        return $hours > 0 ? "{$days}d {$hours}h" : "{$days}d";
    }

    /**
     * @param  list<SiteVisitOutcome|ScheduledActivityOutcome>  $outcomes
     * @return array{total: int, points: list<array{key: string, label: string, count: int, color: string}>}
     */
    private function outcomeSummary(DashboardPeriodFilter $filter, LeadScheduledEventType $type, array $outcomes): array
    {
        [$from, $to] = $filter->dateBounds();

        $query = LeadScheduledEvent::query()
            ->where('type', $type)
            ->where('status', LeadScheduledEventStatus::Completed)
            ->whereNotNull('completion_outcome');

        $this->constrainEventAssignees($query, $filter);
        QueryableDate::constrain($query, 'completed_at', $from, $to);

        $counts = $query
            ->toBase()
            ->select('completion_outcome', DB::raw('count(*) as aggregate'))
            ->groupBy('completion_outcome')
            ->pluck('aggregate', 'completion_outcome')
            ->map(fn (mixed $count): int => (int) $count);

        $points = collect($outcomes)
            ->values()
            ->map(function (SiteVisitOutcome|ScheduledActivityOutcome $outcome, int $index) use ($counts): array {
                return [
                    'key' => $outcome->value,
                    'label' => $outcome->label(),
                    'count' => (int) $counts->get($outcome->value, 0),
                    'color' => self::OutcomePalette[$index % count(self::OutcomePalette)],
                ];
            })
            ->all();

        return [
            'total' => (int) collect($points)->sum('count'),
            'points' => $points,
        ];
    }

    /**
     * @return Builder<Lead>
     */
    private function leadQueryForPeriod(DashboardPeriodFilter $filter, string $column): Builder
    {
        [$from, $to] = $filter->dateBounds();

        $query = $this->leadQuery($filter);
        QueryableDate::constrain($query, $column, $from, $to);

        return $query;
    }

    /**
     * @return Builder<Lead>
     */
    private function leadQuery(DashboardPeriodFilter $filter): Builder
    {
        $query = Lead::query()->withoutGlobalScope(LeadVisibilityScope::class);
        $this->constrainAssignees($query, $filter, 'assigned_to_id');

        return $query;
    }

    /**
     * @param  Builder<Lead>|QueryBuilder  $query
     */
    private function constrainAssignees(Builder|QueryBuilder $query, DashboardPeriodFilter $filter, string $column): void
    {
        $assigneeIds = $this->assigneeIdsForFilter($filter);

        if ($assigneeIds === null) {
            return;
        }

        if ($assigneeIds === []) {
            $query->whereRaw('0 = 1');

            return;
        }

        $query->whereIn($column, $assigneeIds);
    }

    /**
     * @param  Builder<LeadScheduledEvent>  $query
     */
    private function constrainEventAssignees(Builder $query, DashboardPeriodFilter $filter): void
    {
        $assigneeIds = $this->assigneeIdsForFilter($filter);

        if ($assigneeIds === null) {
            return;
        }

        $query->whereHas('lead', function (Builder $leadQuery) use ($assigneeIds): void {
            $leadQuery->withoutGlobalScope(LeadVisibilityScope::class);

            if ($assigneeIds === []) {
                $leadQuery->whereRaw('0 = 1');

                return;
            }

            $leadQuery->whereIn('assigned_to_id', $assigneeIds);
        });
    }

    /**
     * @return list<int>|null
     */
    private function assigneeIdsForFilter(DashboardPeriodFilter $filter): ?array
    {
        if ($filter->teamId === null && $filter->userId === null) {
            return null;
        }

        $teamMemberIds = null;

        if ($filter->teamId !== null) {
            $team = SalesTeam::query()->find($filter->teamId);

            if ($team === null) {
                return [];
            }

            $memberIds = $team->members()->pluck('users.id');

            if ($team->manager_id !== null) {
                $memberIds->push($team->manager_id);
            }

            $teamMemberIds = $memberIds
                ->unique()
                ->values()
                ->map(fn (mixed $id): int => (int) $id)
                ->all();
        }

        if ($filter->userId !== null) {
            if ($teamMemberIds !== null && ! in_array($filter->userId, $teamMemberIds, true)) {
                return [];
            }

            return [$filter->userId];
        }

        return $teamMemberIds ?? [];
    }
}
