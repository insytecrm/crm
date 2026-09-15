<?php

namespace App\Queries;

use App\Enums\PlatformLeadStage;
use App\Models\PlatformLead;

class PlatformLeadStatistics
{
    /**
     * @return array{
     *     total: int,
     *     new_leads: int,
     *     demos: int,
     *     trials: int,
     *     quoted: int,
     *     paid: int,
     *     live: int,
     * }
     */
    public function summary(): array
    {
        return [
            'total' => PlatformLead::query()->count(),
            'new_leads' => PlatformLead::query()->where('stage', PlatformLeadStage::NewLead)->count(),
            'demos' => PlatformLead::query()->where('stage', PlatformLeadStage::Demo)->count(),
            'trials' => PlatformLead::query()->where('stage', PlatformLeadStage::Trial)->count(),
            'quoted' => PlatformLead::query()->where('stage', PlatformLeadStage::Quoted)->count(),
            'paid' => PlatformLead::query()->where('stage', PlatformLeadStage::Paid)->count(),
            'live' => PlatformLead::query()->whereIn('stage', [
                PlatformLeadStage::Live,
                PlatformLeadStage::Retention,
            ])->count(),
        ];
    }
}
