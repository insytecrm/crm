<?php

namespace App\Queries;

use App\Actions\RecordLeadScheduledEvent;
use App\Enums\ActivityFilter;
use App\Enums\ActivityKind;
use App\Enums\LeadScheduledEventStatus;
use App\Enums\LeadScheduledEventType;
use App\Enums\LeadStatus;
use App\Enums\ScheduledActivityStage;
use App\Models\Lead;
use App\Models\LeadScheduledEvent;
use Carbon\CarbonInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class ScheduledActivities
{
    public function __construct(private RecordLeadScheduledEvent $recordLeadScheduledEvent) {}

    /**
     * @return LengthAwarePaginator<int, array{
     *     event_id: int,
     *     kind: string,
     *     lead: Lead,
     *     occurred_at: CarbonInterface,
     *     scheduled_at: CarbonInterface,
     *     is_overdue: bool,
     *     is_completed: bool,
     *     notes: ?string,
     *     label: string,
     * }>
     */
    public function paginateByTypeAndStage(
        LeadScheduledEventType $type,
        ScheduledActivityStage $stage,
        ?string $search = null,
        int $perPage = 15,
    ): LengthAwarePaginator {
        $now = now();

        $query = LeadScheduledEvent::query()
            ->with(['lead.assignedTo', 'property'])
            ->where('type', $type)
            ->whereHas('lead', fn (Builder $query): Builder => $query->whereNotIn('status', [LeadStatus::Converted, LeadStatus::Lost]));

        match ($stage) {
            ScheduledActivityStage::Completed => $query->where('status', LeadScheduledEventStatus::Completed),
            ScheduledActivityStage::Overdue => $query
                ->where('status', LeadScheduledEventStatus::Scheduled)
                ->where('scheduled_at', '<', $now),
            ScheduledActivityStage::Pending => $query
                ->where('status', LeadScheduledEventStatus::Scheduled)
                ->where('scheduled_at', '>=', $now),
            ScheduledActivityStage::Rescheduled => $query
                ->where('status', LeadScheduledEventStatus::Scheduled)
                ->whereNotNull('rescheduled_at'),
        };

        if ($search !== null && $search !== '') {
            $query->whereHas('lead', function (Builder $query) use ($search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            });
        }

        if ($stage === ScheduledActivityStage::Completed) {
            $query->latest('completed_at')->latest('scheduled_at');
        } else {
            $query
                ->orderByRaw("CASE priority WHEN 'high' THEN 0 WHEN 'normal' THEN 1 WHEN 'low' THEN 2 ELSE 1 END")
                ->orderBy('scheduled_at');
        }

        return $query
            ->paginate($perPage)
            ->withQueryString()
            ->through(fn (LeadScheduledEvent $event): array => $this->mapEvent($event));
    }

    /**
     * @return array{
     *     pending: int,
     *     overdue: int,
     *     completed: int,
     *     rescheduled: int,
     * }
     */
    public function stageStatistics(LeadScheduledEventType $type): array
    {
        $now = now();
        $baseQuery = LeadScheduledEvent::query()
            ->where('type', $type)
            ->whereHas('lead', fn (Builder $query): Builder => $query->whereNotIn('status', [LeadStatus::Converted, LeadStatus::Lost]));

        return [
            ScheduledActivityStage::Pending->value => (clone $baseQuery)
                ->where('status', LeadScheduledEventStatus::Scheduled)
                ->where('scheduled_at', '>=', $now)
                ->count(),
            ScheduledActivityStage::Overdue->value => (clone $baseQuery)
                ->where('status', LeadScheduledEventStatus::Scheduled)
                ->where('scheduled_at', '<', $now)
                ->count(),
            ScheduledActivityStage::Completed->value => (clone $baseQuery)
                ->where('status', LeadScheduledEventStatus::Completed)
                ->count(),
            ScheduledActivityStage::Rescheduled->value => (clone $baseQuery)
                ->where('status', LeadScheduledEventStatus::Scheduled)
                ->whereNotNull('rescheduled_at')
                ->count(),
        ];
    }

    /**
     * @return array{
     *     total_activities: int,
     *     site_visits: int,
     *     follow_ups: int,
     * }
     */
    public function statistics(ActivityFilter $filter): array
    {
        $items = $this->items($filter);

        $followUps = $items->where('kind', ActivityKind::FollowUp->value)->count();
        $siteVisits = $items->where('kind', ActivityKind::SiteVisit->value)->count();

        return [
            'total_activities' => $siteVisits + $followUps,
            'site_visits' => $siteVisits,
            'follow_ups' => $followUps,
        ];
    }

    /**
     * @return Collection<int, array{
     *     kind: string,
     *     lead: Lead,
     *     occurred_at: CarbonInterface,
     *     is_overdue: bool,
     *     is_completed: bool,
     *     notes: ?string,
     *     label: string,
     * }>
     */
    public function items(ActivityFilter $filter, ?ActivityKind $kind = null): Collection
    {
        $items = match ($filter) {
            ActivityFilter::Completed => $this->completedItems(),
            ActivityFilter::All => $this->pendingItems(ActivityFilter::All)
                ->concat($this->completedItems()),
            ActivityFilter::Today => $this->pendingItems(ActivityFilter::Today)
                ->concat($this->completedItems(ActivityFilter::Today)),
            default => $this->pendingItems($filter),
        };

        $items = $this->sortItems($items, $filter);

        if ($kind !== null) {
            $items = $items->where('kind', $kind->value);
        }

        return $items
            ->filter(fn (array $item): bool => $item['lead'] !== null)
            ->values();
    }

    /**
     * @return Collection<int, array{
     *     kind: string,
     *     lead: Lead,
     *     occurred_at: CarbonInterface,
     *     is_overdue: bool,
     *     is_completed: bool,
     *     notes: ?string,
     *     label: string,
     * }>
     */
    private function pendingItems(ActivityFilter $filter): Collection
    {
        $this->backfillMissingScheduledEvents();

        $now = now();
        $startOfDay = $now->copy()->startOfDay();
        $endOfDay = $now->copy()->endOfDay();

        return LeadScheduledEvent::query()
            ->with(['lead.assignedTo', 'property'])
            ->where('status', LeadScheduledEventStatus::Scheduled)
            ->whereHas('lead', fn (Builder $query): Builder => $query->whereNotIn('status', [LeadStatus::Converted, LeadStatus::Lost]))
            ->get()
            ->filter(fn (LeadScheduledEvent $event): bool => $this->matchesPendingFilter($event->scheduled_at, $filter, $now, $startOfDay, $endOfDay))
            ->map(fn (LeadScheduledEvent $event): array => $this->mapEvent($event))
            ->values();
    }

    /**
     * @return Collection<int, array{
     *     kind: string,
     *     lead: Lead,
     *     occurred_at: CarbonInterface,
     *     is_overdue: bool,
     *     is_completed: bool,
     *     notes: ?string,
     *     label: string,
     * }>
     */
    private function completedItems(?ActivityFilter $dateFilter = null): Collection
    {
        $startOfDay = now()->startOfDay();
        $endOfDay = now()->endOfDay();

        return LeadScheduledEvent::query()
            ->with(['lead.assignedTo', 'property'])
            ->where('status', LeadScheduledEventStatus::Completed)
            ->whereHas('lead')
            ->get()
            ->filter(function (LeadScheduledEvent $event) use ($dateFilter, $startOfDay, $endOfDay): bool {
                if ($dateFilter !== ActivityFilter::Today) {
                    return true;
                }

                $completedAt = $event->completed_at ?? $event->scheduled_at;

                return $completedAt->between($startOfDay, $endOfDay);
            })
            ->map(fn (LeadScheduledEvent $event): array => $this->mapEvent($event))
            ->values();
    }

    /**
     * @return array{
     *     kind: string,
     *     lead: Lead,
     *     occurred_at: CarbonInterface,
     *     is_overdue: bool,
     *     is_completed: bool,
     *     notes: ?string,
     *     label: string,
     * }
     */
    private function mapEvent(LeadScheduledEvent $event): array
    {
        $now = now();
        $isCompleted = $event->status === LeadScheduledEventStatus::Completed;
        $occurredAt = $isCompleted
            ? ($event->completed_at ?? $event->scheduled_at)
            : $event->scheduled_at;

        return [
            'event_id' => $event->id,
            'kind' => match ($event->type) {
                LeadScheduledEventType::FollowUp => ActivityKind::FollowUp->value,
                LeadScheduledEventType::SiteVisit => ActivityKind::SiteVisit->value,
            },
            'lead' => $event->lead,
            'occurred_at' => $occurredAt,
            'scheduled_at' => $event->scheduled_at,
            'is_overdue' => ! $isCompleted && $event->scheduled_at->lt($now),
            'is_completed' => $isCompleted,
            'notes' => $event->notes,
            'label' => $event->ordinalLabel(),
            'priority' => $event->priority,
            'completion_method' => $event->type === LeadScheduledEventType::SiteVisit
                ? $event->attendedLabel()
                : $event->completion_method?->label(),
            'completion_outcome' => $event->completion_outcome?->label(),
            'next_step_type' => $event->next_step_type?->label(),
            'property_label' => $event->type === LeadScheduledEventType::SiteVisit
                ? $event->property?->listLabel()
                : null,
        ];
    }

    /**
     * @param  Collection<int, array{
     *     kind: string,
     *     lead: Lead,
     *     occurred_at: CarbonInterface,
     *     is_overdue: bool,
     *     is_completed: bool,
     *     notes: ?string,
     *     label: string,
     * }>  $items
     * @return Collection<int, array{
     *     kind: string,
     *     lead: Lead,
     *     occurred_at: CarbonInterface,
     *     is_overdue: bool,
     *     is_completed: bool,
     *     notes: ?string,
     *     label: string,
     * }>
     */
    private function sortItems(Collection $items, ActivityFilter $filter): Collection
    {
        return match ($filter) {
            ActivityFilter::Completed, ActivityFilter::All => $items
                ->sortByDesc(fn (array $item): int => $item['occurred_at']->getTimestamp())
                ->values(),
            default => $items->sortBy('occurred_at')->values(),
        };
    }

    private function matchesPendingFilter(
        CarbonInterface $scheduledAt,
        ActivityFilter $filter,
        CarbonInterface $now,
        CarbonInterface $startOfDay,
        CarbonInterface $endOfDay,
    ): bool {
        return match ($filter) {
            ActivityFilter::Today => $scheduledAt->between($startOfDay, $endOfDay),
            ActivityFilter::Upcoming => $scheduledAt->gt($endOfDay),
            ActivityFilter::Overdue => $scheduledAt->lt($now),
            ActivityFilter::All => true,
            ActivityFilter::Completed => false,
        };
    }

    private function backfillMissingScheduledEvents(): void
    {
        Lead::query()
            ->whereNotNull('next_follow_up_at')
            ->whereNotIn('status', [LeadStatus::Converted, LeadStatus::Lost])
            ->whereDoesntHave('scheduledEvents', function (Builder $query): void {
                $query
                    ->where('type', LeadScheduledEventType::FollowUp)
                    ->where('status', LeadScheduledEventStatus::Scheduled);
            })
            ->each(function (Lead $lead): void {
                $this->recordLeadScheduledEvent->schedule(
                    $lead,
                    LeadScheduledEventType::FollowUp,
                    $lead->next_follow_up_at,
                );
            });

        Lead::query()
            ->whereNotNull('upcoming_site_visit_at')
            ->whereNotIn('status', [LeadStatus::Converted, LeadStatus::Lost])
            ->whereDoesntHave('scheduledEvents', function (Builder $query): void {
                $query
                    ->where('type', LeadScheduledEventType::SiteVisit)
                    ->where('status', LeadScheduledEventStatus::Scheduled);
            })
            ->each(function (Lead $lead): void {
                $this->recordLeadScheduledEvent->schedule(
                    $lead,
                    LeadScheduledEventType::SiteVisit,
                    $lead->upcoming_site_visit_at,
                );
            });
    }
}
