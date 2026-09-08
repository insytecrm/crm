<?php

namespace App\Actions;

use App\Enums\PropertyPortal;
use App\Models\PortalWebhookEndpoint;
use App\Models\Tenant;
use Illuminate\Support\Str;

class IssuePortalWebhookSecret
{
    public function handle(PortalWebhookEndpoint $endpoint): string
    {
        $plainTextSecret = 'whsec_'.Str::random(40);

        $endpoint->update([
            'secret_hash' => hash('sha256', $plainTextSecret),
            'secret_encrypted' => $plainTextSecret,
            'secret_last_four' => substr($plainTextSecret, -4),
            'generated_at' => now(),
            'last_used_at' => null,
            'is_active' => true,
        ]);

        return $plainTextSecret;
    }

    public function createFor(Tenant $tenant, PropertyPortal $portal): PortalWebhookEndpoint
    {
        $plainTextSecret = 'whsec_'.Str::random(40);

        return PortalWebhookEndpoint::query()->create([
            'tenant_id' => $tenant->getTenantKey(),
            'portal' => $portal,
            'webhook_id' => 'wh_'.Str::lower(Str::random(24)),
            'secret_hash' => hash('sha256', $plainTextSecret),
            'secret_encrypted' => $plainTextSecret,
            'secret_last_four' => substr($plainTextSecret, -4),
            'is_active' => true,
            'generated_at' => now(),
            'last_used_at' => null,
        ]);
    }
}
