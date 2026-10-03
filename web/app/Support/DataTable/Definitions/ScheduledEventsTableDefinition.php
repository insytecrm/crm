<?php

namespace App\Support\DataTable\Definitions;

use App\Actions\DeleteLeadScheduledEvents;
use App\Models\LeadScheduledEvent;
use App\Support\DataTable\AbstractDataTableDefinition;

abstract class ScheduledEventsTableDefinition extends AbstractDataTableDefinition
{
    public function modelClass(): string
    {
        return LeadScheduledEvent::class;
    }

    public function bulkDeleteParameterName(): string
    {
        return 'event_ids';
    }

    /**
     * @param  list<int>  $ids
     */
    public function bulkDelete(array $ids): int
    {
        return app(DeleteLeadScheduledEvents::class)->handle($ids);
    }
}
