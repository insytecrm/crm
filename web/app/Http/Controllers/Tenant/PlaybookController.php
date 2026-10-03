<?php

namespace App\Http\Controllers\Tenant;

use App\Actions\InstallPlaybook;
use App\Enums\TenantPermission;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PlaybookController extends Controller
{
    public function store(Request $request, InstallPlaybook $installPlaybook): RedirectResponse
    {
        abort_unless($request->user()?->hasPermission(TenantPermission::AutomationsManage), 403);

        $validated = $request->validate([
            'playbook' => ['required', 'string', Rule::in(array_keys(config('playbooks', [])))],
        ]);

        $installPlaybook->handle($request->user(), $validated['playbook'], true);

        return redirect()
            ->route('tenant.automations.index')
            ->with('status', __('Playbook enabled.'));
    }

    public function destroy(Request $request, string $playbook, InstallPlaybook $installPlaybook): RedirectResponse
    {
        abort_unless($request->user()?->hasPermission(TenantPermission::AutomationsManage), 403);

        $installPlaybook->handle($request->user(), $playbook, false);

        return redirect()
            ->route('tenant.automations.index')
            ->with('status', __('Playbook disabled.'));
    }
}
