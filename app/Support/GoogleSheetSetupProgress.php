<?php

namespace App\Support;

use App\Enums\GoogleSheetConnectionStatus;
use App\Models\GoogleSheetConnection;

class GoogleSheetSetupProgress
{
    /**
     * @return array{step: int, total: int}
     */
    public function for(GoogleSheetConnection $connection): array
    {
        $step = match ($connection->status) {
            GoogleSheetConnectionStatus::Draft => 1,
            GoogleSheetConnectionStatus::Verified => 2,
            GoogleSheetConnectionStatus::Connected, GoogleSheetConnectionStatus::Paused => 4,
        };

        return [
            'step' => $step,
            'total' => 4,
        ];
    }
}
