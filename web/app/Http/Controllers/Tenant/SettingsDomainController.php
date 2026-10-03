<?php

namespace App\Http\Controllers\Tenant;

use App\Actions\RemoveTenantDomain;
use App\Actions\UpsertTenantDomain;
use App\Actions\VerifyTenantDomain;
use App\Enums\DomainPurpose;
use App\Enums\SettingsTab;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\UpsertSettingsDomainRequest;
use App\Http\Requests\Tenant\VerifySettingsDomainRequest;
use App\Models\Tenant;
use Illuminate\Http\RedirectResponse;

class SettingsDomainController extends Controller
{
    public function upsert(UpsertSettingsDomainRequest $request, UpsertTenantDomain $upsertTenantDomain): RedirectResponse
    {
        /** @var Tenant $tenant */
        $tenant = tenant();
        $purpose = DomainPurpose::from($request->validated('purpose'));

        $upsertTenantDomain->handle($tenant, $purpose, $request->validated('domain'));

        return redirect()
            ->route('tenant.settings.index', ['tab' => SettingsTab::Domains->value])
            ->with('status', __('Domain saved. Add the DNS records below, then verify.'));
    }

    public function verify(VerifySettingsDomainRequest $request, VerifyTenantDomain $verifyTenantDomain): RedirectResponse
    {
        /** @var Tenant $tenant */
        $tenant = tenant();
        $purpose = DomainPurpose::from($request->validated('purpose'));
        $domain = $tenant->domainFor($purpose);

        abort_if($domain === null, 404);

        $verifyTenantDomain->handle($domain);

        $message = $purpose === DomainPurpose::Website
            ? __('Website domain verified. Property microsites can now be published on this domain.')
            : __('CRM domain verified. You can access the CRM from this domain.');

        return redirect()
            ->route('tenant.settings.index', ['tab' => SettingsTab::Domains->value])
            ->with('status', $message);
    }

    public function destroy(VerifySettingsDomainRequest $request, RemoveTenantDomain $removeTenantDomain): RedirectResponse
    {
        /** @var Tenant $tenant */
        $tenant = tenant();
        $purpose = DomainPurpose::from($request->validated('purpose'));

        $removeTenantDomain->handle($tenant, $purpose);

        return redirect()
            ->route('tenant.settings.index', ['tab' => SettingsTab::Domains->value])
            ->with('status', __('Domain removed.'));
    }
}
