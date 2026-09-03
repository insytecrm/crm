<?php

namespace App\Queries;

use App\Enums\ActivityFilter;
use App\Models\Lead;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class DashboardMyDay
{
    public function __construct(
        private ScheduledActivities $scheduledActivities,
    ) {}

    /**
     * Today's incomplete follow-ups and site visits, plus overdue items.
     *
     * @return Collection<int, array{
     *     event_id: int,
     *     kind: string,
     *     lead: Lead,
     *     occurred_at: CarbonInterface,
     *     scheduled_at: CarbonInterface,
     *     is_overdue: bool,
     *     is_completed: bool,
     *     notes: ?string,
     *     label: string,
     *     priority: mixed,
     *     property_label: ?string,
     * }>
     */
    public function forTenant(): Collection
    {
        $todayPending = $this->scheduledActivities
            ->items(ActivityFilter::Today)
            ->filter(fn (array $item): bool => ! ($item['is_completed'] ?? false));

        $overdue = $this->scheduledActivities->items(ActivityFilter::Overdue);

        return $todayPending
            ->concat($overdue)
            ->unique('event_id')
            ->sortBy(fn (array $item): array => [
                $item['is_overdue'] ? 0 : 1,
                $item['occurred_at']->getTimestamp(),
            ])
            ->values();
    }
}
