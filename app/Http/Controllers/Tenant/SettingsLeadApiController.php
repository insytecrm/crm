<?php

namespace App\Http\Controllers\Tenant;

use App\Actions\IssueTenantLeadApiToken;
use App\Enums\TenantPermission;
use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SettingsLeadApiController extends Controller
{
    public function show(IssueTenantLeadApiToken $issueTenantLeadApiToken): View
    {
        abort_unless(auth()->user()?->hasPermission(TenantPermission::IntegrationsView), 403);

        /** @var Tenant $tenant */
        $tenant = tenant();

        if ($tenant->lead_api_token_hash === null) {
            $issueTenantLeadApiToken->handle($tenant);
            $tenant->refresh();
        }

        return view('tenant.settings.integrations.api', [
            'tenant' => $tenant,
            'apiUrl' => route('api.v1.leads.store'),
            'apiKey' => $tenant->lead_api_token_encrypted,
            'canManage' => auth()->user()->hasPermission(TenantPermission::IntegrationsManage),
        ]);
    }

    public function regenerate(IssueTenantLeadApiToken $issueTenantLeadApiToken): RedirectResponse
    {
        abort_unless(auth()->user()?->hasPermission(TenantPermission::IntegrationsManage), 403);

        /** @var Tenant $tenant */
        $tenant = tenant();

        $issueTenantLeadApiToken->handle($tenant);

        return redirect()
            ->route('tenant.settings.integrations.api')
            ->with('status', __('API key regenerated. Update any third-party systems that used the old key.'));
    }
}
