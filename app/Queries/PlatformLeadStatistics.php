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
     *     quotations: int,
     *     onboarding: int,
     *     client_live: int,
     * }
     */
    public function summary(): array
    {
        return [
            'total' => PlatformLead::query()->count(),
            'new_leads' => PlatformLead::query()->where('stage', PlatformLeadStage::NewLead)->count(),
            'demos' => PlatformLead::query()->whereIn('stage', [
                PlatformLeadStage::DemoScheduled,
                PlatformLeadStage::DemoCompleted,
            ])->count(),
            'quotations' => PlatformLead::query()->whereIn('stage', [
                PlatformLeadStage::QuotationSent,
                PlatformLeadStage::QuotationAccepted,
            ])->count(),
            'onboarding' => PlatformLead::query()->where('stage', PlatformLeadStage::Onboarding)->count(),
            'client_live' => PlatformLead::query()->whereIn('stage', [
                PlatformLeadStage::ClientLive,
                PlatformLeadStage::Retention,
            ])->count(),
        ];
    }
}
