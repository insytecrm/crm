<?php

namespace App\Http\Controllers\Tenant;

use App\Actions\EnsurePortalWebhookEndpoint;
use App\Actions\IssuePortalWebhookSecret;
use App\Enums\PropertyPortal;
use App\Enums\TenantPermission;
use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Queries\PortalWebhookLeadStats;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SettingsPortalWebhookController extends Controller
{
    public function show(
        string $portal,
        EnsurePortalWebhookEndpoint $ensurePortalWebhookEndpoint,
        PortalWebhookLeadStats $portalWebhookLeadStats,
    ): View {
        abort_unless(auth()->user()?->hasPermission(TenantPermission::IntegrationsView), 403);

        $portalEnum = PropertyPortal::tryFrom($portal);
        abort_if($portalEnum === null, 404);

        /** @var Tenant $tenant */
        $tenant = tenant();

        $endpoint = $ensurePortalWebhookEndpoint->handle($tenant, $portalEnum);
        $stats = $portalWebhookLeadStats->handle($portalEnum);

        return view('tenant.settings.integrations.portal', [
            'portal' => $portalEnum,
            'endpoint' => $endpoint,
            'webhookUrl' => $endpoint->webhookUrl(),
            'webhookSecret' => $endpoint->plainTextSecret(),
            'stats' => $stats,
            'canManage' => auth()->user()->hasPermission(TenantPermission::IntegrationsManage),
        ]);
    }

    public function regenerate(
        string $portal,
        EnsurePortalWebhookEndpoint $ensurePortalWebhookEndpoint,
        IssuePortalWebhookSecret $issuePortalWebhookSecret,
    ): RedirectResponse {
        abort_unless(auth()->user()?->hasPermission(TenantPermission::IntegrationsManage), 403);

        $portalEnum = PropertyPortal::tryFrom($portal);
        abort_if($portalEnum === null, 404);

        /** @var Tenant $tenant */
        $tenant = tenant();

        $endpoint = $ensurePortalWebhookEndpoint->handle($tenant, $portalEnum);
        $issuePortalWebhookSecret->handle($endpoint);

        return redirect()
            ->route('tenant.settings.integrations.portal', ['portal' => $portalEnum->value])
            ->with('status', __('Webhook secret regenerated. Update :portal with the new secret.', [
                'portal' => $portalEnum->label(),
            ]));
    }
}
