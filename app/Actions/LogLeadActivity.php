<?php

namespace App\Actions;

use App\Enums\LeadActivityType;
use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\User;

class LogLeadActivity
{
    /**
     * @param  array<string, mixed>  $metadata
     */
    public function handle(
        Lead $lead,
        LeadActivityType $type,
        string $description,
        ?User $user = null,
        array $metadata = [],
    ): LeadActivity {
        $activity = $lead->activities()->create([
            'user_id' => $user?->id ?? auth()->id(),
            'type' => $type,
            'description' => $description,
            'metadata' => $metadata ?: null,
        ]);

        $lead->update(['last_activity_at' => now()]);

        return $activity;
    }
}
