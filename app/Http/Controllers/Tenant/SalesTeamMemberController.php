<?php

namespace App\Http\Controllers\Tenant;

use App\Enums\TenantPermission;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\StoreSalesTeamMemberRequest;
use App\Models\SalesTeam;
use App\Models\User;
use Illuminate\Http\RedirectResponse;

class SalesTeamMemberController extends Controller
{
    public function store(StoreSalesTeamMemberRequest $request, SalesTeam $team): RedirectResponse
    {
        $team->members()->attach($request->validated('user_id'));

        return redirect()
            ->route('tenant.teams.show', $team)
            ->with('status', __('Member added successfully.'));
    }

    public function destroy(SalesTeam $team, User $user): RedirectResponse
    {
        abort_unless(auth()->user()?->hasPermission(TenantPermission::TeamsManage), 403);

        if (! $team->members()->whereKey($user->id)->exists()) {
            abort(404);
        }

        $team->members()->detach($user->id);

        return redirect()
            ->route('tenant.teams.show', $team)
            ->with('status', __('Member removed successfully.'));
    }
}
