<?php

namespace App\Http\Controllers\Tenant;

use App\Actions\SaveLeadRoutingRule;
use App\Enums\TenantPermission;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\StoreLeadRoutingRuleRequest;
use App\Http\Requests\Tenant\UpdateLeadRoutingRuleRequest;
use App\Models\LeadRoutingRule;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LeadRoutingRuleController extends Controller
{
    public function store(StoreLeadRoutingRuleRequest $request, SaveLeadRoutingRule $saveLeadRoutingRule): RedirectResponse
    {
        $saveLeadRoutingRule->handle($request->ruleData(), actor: $request->user());

        return redirect()
            ->route('tenant.teams.index')
            ->with('status', __('Routing rule saved.'));
    }

    public function update(
        UpdateLeadRoutingRuleRequest $request,
        LeadRoutingRule $routingRule,
        SaveLeadRoutingRule $saveLeadRoutingRule,
    ): RedirectResponse {
        $saveLeadRoutingRule->handle($request->ruleData(), $routingRule, $request->user());

        return redirect()
            ->route('tenant.teams.index')
            ->with('status', __('Routing rule updated.'));
    }

    public function destroy(Request $request, LeadRoutingRule $routingRule): RedirectResponse
    {
        abort_unless($request->user()?->hasPermission(TenantPermission::TeamsManage), 403);

        $routingRule->delete();

        return redirect()
            ->route('tenant.teams.index')
            ->with('status', __('Routing rule deleted.'));
    }
}
