<?php

namespace App\Actions;

use App\Enums\PropertyPortal;
use App\Models\PortalWebhookEndpoint;
use App\Models\Tenant;

class EnsurePortalWebhookEndpoint
{
    public function __construct(
        private IssuePortalWebhookSecret $issuePortalWebhookSecret,
    ) {}

    public function handle(Tenant $tenant, PropertyPortal $portal): PortalWebhookEndpoint
    {
        $endpoint = PortalWebhookEndpoint::query()
            ->where('tenant_id', $tenant->getTenantKey())
            ->where('portal', $portal->value)
            ->first();

        if ($endpoint instanceof PortalWebhookEndpoint) {
            return $endpoint;
        }

        return $this->issuePortalWebhookSecret->createFor($tenant, $portal);
    }
}
