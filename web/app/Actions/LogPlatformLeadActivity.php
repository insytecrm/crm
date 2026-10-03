<?php

namespace App\Actions;

use App\Enums\PlatformLeadActivityType;
use App\Models\PlatformLead;
use App\Models\PlatformLeadActivity;
use App\Models\User;

class LogPlatformLeadActivity
{
    /**
     * @param  array<string, mixed>  $metadata
     */
    public function handle(
        PlatformLead $lead,
        PlatformLeadActivityType $type,
        string $description,
        ?User $user = null,
        array $metadata = [],
    ): PlatformLeadActivity {
        return $lead->activities()->create([
            'user_id' => $user?->id ?? auth()->id(),
            'type' => $type,
            'description' => $description,
            'metadata' => $metadata ?: null,
        ]);
    }
}
