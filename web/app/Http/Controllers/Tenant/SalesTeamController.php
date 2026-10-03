<?php

namespace App\Http\Controllers\Tenant;

use App\Actions\AssertPlanLimit;
use App\Enums\LeadRoutingDistribution;
use App\Enums\LeadSource;
use App\Enums\PlanLimitKey;
use App\Enums\TenantPermission;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\StoreSalesTeamRequest;
use App\Http\Requests\Tenant\UpdateSalesTeamRequest;
use App\Http\Requests\Tenant\UpdateSalesTeamStatusRequest;
use App\Models\LeadRoutingRule;
use App\Models\SalesTeam;
use App\Models\User;
use App\Support\DataTable\DataTableViewData;
use App\Support\LeadRoutingSubSourceOptions;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SalesTeamController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()?->hasPermission(TenantPermission::TeamsView), 403);

        $search = $request->string('search')->trim()->toString();

        $teams = SalesTeam::query()
            ->with(['manager'])
            ->withCount('members')
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhereHas('manager', fn ($managerQuery) => $managerQuery->where('name', 'like', "%{$search}%"));
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $canManage = $request->user()?->hasPermission(TenantPermission::TeamsManage) ?? false;

        $routingTeams = $canManage
            ? SalesTeam::query()
                ->active()
                ->with(['members' => fn ($query) => $query
                    ->where('users.is_active', true)
                    ->with('role')
                    ->orderBy('name')])
                ->orderBy('name')
                ->get()
            : collect();

        $routingRules = LeadRoutingRule::query()
            ->with(['team', 'members'])
            ->latest('id')
            ->get();

        return view('tenant.teams.index', array_merge([
            'teams' => $teams,
            'search' => $search,
            'canManage' => $canManage,
            'managers' => $canManage ? $this->managerOptions() : collect(),
            'openCreateTeam' => old('_create_team') === '1',
            'openCreateRouting' => old('_create_routing') === '1',
            'openEditRoutingId' => old('_edit_routing_id'),
            'routingTeams' => $routingTeams,
            'routingRules' => $routingRules,
            'routingSources' => LeadSource::cases(),
            'routingDistributions' => LeadRoutingDistribution::cases(),
            'routingSubSources' => $canManage ? app(LeadRoutingSubSourceOptions::class)->all() : [],
        ], DataTableViewData::for($request->user(), 'teams', $teams)));
    }

    public function show(Request $request, SalesTeam $team): View
    {
        abort_unless($request->user()?->hasPermission(TenantPermission::TeamsView), 403);

        $team->load(['manager', 'members.role', 'createdBy']);

        $canManage = $request->user()?->hasPermission(TenantPermission::TeamsManage) ?? false;

        return view('tenant.teams.show', [
            'team' => $team,
            'canManage' => $canManage,
            'managers' => $canManage ? $this->managerOptions() : collect(),
            'memberCandidates' => $canManage ? $this->memberCandidates($team) : collect(),
            'openEditTeam' => $request->boolean('edit') || (string) old('_edit_team_id') === (string) $team->id,
        ]);
    }

    public function store(StoreSalesTeamRequest $request): RedirectResponse
    {
        app(AssertPlanLimit::class)->handle(PlanLimitKey::Teams);

        $team = SalesTeam::query()->create([
            'name' => $request->validated('name'),
            'description' => $request->validated('description'),
            'manager_id' => $request->validated('manager_id'),
            'is_active' => $request->isActive(),
            'created_by_id' => $request->user()?->id,
        ]);

        $memberIds = $request->memberIds();

        if ($memberIds !== []) {
            $team->members()->attach($memberIds);
        }

        return redirect()
            ->route('tenant.teams.show', $team)
            ->with('status', __('Team created successfully.'));
    }

    public function update(UpdateSalesTeamRequest $request, SalesTeam $team): RedirectResponse
    {
        $team->update([
            'name' => $request->validated('name'),
            'description' => $request->validated('description'),
            'manager_id' => $request->validated('manager_id'),
            'is_active' => $request->isActive(),
        ]);

        return redirect()
            ->route('tenant.teams.show', $team)
            ->with('status', __('Team updated successfully.'));
    }

    public function updateStatus(UpdateSalesTeamStatusRequest $request, SalesTeam $team): RedirectResponse
    {
        $team->update([
            'is_active' => $request->isActive(),
        ]);

        $message = $request->isActive()
            ? __('Team activated.')
            : __('Team deactivated.');

        return redirect()
            ->back()
            ->with('status', $message);
    }

    public function destroy(SalesTeam $team): RedirectResponse
    {
        abort_unless(auth()->user()?->hasPermission(TenantPermission::TeamsManage), 403);

        $team->delete();

        return redirect()
            ->route('tenant.teams.index')
            ->with('status', __('Team archived successfully.'));
    }

    /**
     * @return Collection<int, User>
     */
    private function managerOptions()
    {
        return User::query()
            ->with('role')
            ->where('is_active', true)
            ->whereHas('role', fn ($query) => $query
                ->where('is_active', true)
                ->whereIn('slug', ['manager', 'administrator']))
            ->orderBy('name')
            ->get();
    }

    /**
     * @return Collection<int, User>
     */
    private function memberCandidates(?SalesTeam $excludeTeam = null)
    {
        return User::query()
            ->with('role')
            ->where('is_active', true)
            ->whereDoesntHave('salesTeams', function ($query) use ($excludeTeam): void {
                if ($excludeTeam !== null) {
                    $query->where('sales_teams.id', '!=', $excludeTeam->id);
                }
            })
            ->orderBy('name')
            ->get();
    }
}
