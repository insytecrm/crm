<?php

namespace App\Http\Controllers\Platform;

use App\Contracts\ChannelPartnerProfileData;
use App\Http\Controllers\Controller;
use App\Models\PartnerSubscription;
use App\Models\Plan;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ChannelPartnerController extends Controller
{
    public function overview(Tenant $tenant, ChannelPartnerProfileData $profile): View
    {
        return view('platform.tenants.show', [
            'tenant' => $tenant,
            'shell' => $profile->shell($tenant, 'overview'),
            'overview' => $profile->overview($tenant),
        ]);
    }

    public function users(Tenant $tenant, Request $request, ChannelPartnerProfileData $profile): View
    {
        return view('platform.tenants.users', [
            'tenant' => $tenant,
            'shell' => $profile->shell($tenant, 'users'),
            'users' => $profile->users($tenant, $request),
        ]);
    }

    public function subscription(Tenant $tenant, ChannelPartnerProfileData $profile): View
    {
        $partnerSubscription = PartnerSubscription::query()
            ->with('plan')
            ->where('tenant_id', $tenant->getTenantKey())
            ->latest('id')
            ->first();

        return view('platform.tenants.subscription', [
            'tenant' => $tenant,
            'shell' => $profile->shell($tenant, 'subscription'),
            'subscription' => $profile->subscription($tenant),
            'partnerSubscription' => $partnerSubscription,
            'plans' => Plan::query()->active()->orderBy('price_monthly')->get(),
        ]);
    }

    public function usage(Tenant $tenant, ChannelPartnerProfileData $profile): View
    {
        return view('platform.tenants.usage', [
            'tenant' => $tenant,
            'shell' => $profile->shell($tenant, 'usage'),
            'usage' => $profile->usage($tenant),
        ]);
    }

    public function integrations(Tenant $tenant, ChannelPartnerProfileData $profile): View
    {
        return view('platform.tenants.integrations', [
            'tenant' => $tenant,
            'shell' => $profile->shell($tenant, 'integrations'),
            'integrations' => $profile->integrations($tenant),
        ]);
    }

    public function activity(Tenant $tenant, Request $request, ChannelPartnerProfileData $profile): View
    {
        return view('platform.tenants.activity', [
            'tenant' => $tenant,
            'shell' => $profile->shell($tenant, 'activity'),
            'activity' => $profile->activity($tenant, $request),
        ]);
    }
}
