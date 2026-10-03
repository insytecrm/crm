<?php

namespace App\Queries;

use App\Enums\LeadStatus;
use App\Enums\PropertyPortal;
use App\Models\Lead;
use Illuminate\Support\Collection;

class PortalWebhookLeadStats
{
    /**
     * @return array{
     *     total: int,
     *     new: int,
     *     contacted: int,
     *     converted: int,
     *     recent: Collection<int, Lead>
     * }
     */
    public function handle(PropertyPortal $portal): array
    {
        $source = $portal->leadSource()->value;

        $base = Lead::query()->where('source', $source);

        return [
            'total' => (clone $base)->count(),
            'new' => (clone $base)->where('status', LeadStatus::New)->count(),
            'contacted' => (clone $base)->where('status', LeadStatus::Contacted)->count(),
            'converted' => (clone $base)->where('status', LeadStatus::Converted)->count(),
            'recent' => Lead::query()
                ->where('source', $source)
                ->latest('id')
                ->limit(10)
                ->get(['id', 'name', 'phone', 'email', 'status', 'created_at']),
        ];
    }
}
