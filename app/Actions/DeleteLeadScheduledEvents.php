<?php

namespace App\Actions;

use App\Enums\LeadScheduledEventType;
use App\Models\Lead;
use App\Models\LeadScheduledEvent;
use Illuminate\Support\Facades\DB;

class DeleteLeadScheduledEvents
{
    public function __construct(private RecordLeadScheduledEvent $recordLeadScheduledEvent) {}

    /**
     * @param  list<int>  $ids
     */
    public function handle(array $ids): int
    {
        $ids = array_values(array_unique(array_map('intval', $ids)));

        if ($ids === []) {
            return 0;
        }

        return DB::transaction(function () use ($ids): int {
            $events = LeadScheduledEvent::query()
                ->whereIn('id', $ids)
                ->get(['id', 'lead_id', 'type']);

            if ($events->isEmpty()) {
                return 0;
            }

            $affectedPairs = $events
                ->map(fn (LeadScheduledEvent $event): string => $event->lead_id.'|'.$event->type->value)
                ->unique()
                ->values();

            $deleted = LeadScheduledEvent::query()
                ->whereIn('id', $ids)
                ->delete();

            foreach ($affectedPairs as $pair) {
                [$leadId, $type] = explode('|', $pair, 2);

                $lead = Lead::query()->find((int) $leadId);

                if ($lead === null) {
                    continue;
                }

                $this->recordLeadScheduledEvent->syncLeadScheduledField(
                    $lead,
                    LeadScheduledEventType::from($type),
                );
            }

            return $deleted;
        });
    }
}
