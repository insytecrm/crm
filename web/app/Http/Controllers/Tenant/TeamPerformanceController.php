<?php

namespace App\Http\Controllers\Tenant;

use App\Enums\TenantPermission;
use App\Http\Controllers\Controller;
use App\Models\SalesTeam;
use App\Queries\TeamPerformanceMetrics;
use App\Support\DashboardPeriodFilter;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TeamPerformanceController extends Controller
{
    public function index(Request $request, TeamPerformanceMetrics $metrics): View
    {
        abort_unless($request->user()?->hasPermission(TenantPermission::TeamsView), 403);

        $periodFilter = DashboardPeriodFilter::fromReportsRequest($request);

        return view('tenant.teams.performance.index', [
            'cards' => $metrics->forTeams($periodFilter),
            'periodFilter' => $periodFilter,
        ]);
    }

    public function show(Request $request, SalesTeam $team, TeamPerformanceMetrics $metrics): View
    {
        abort_unless($request->user()?->hasPermission(TenantPermission::TeamsView), 403);

        $periodFilter = DashboardPeriodFilter::fromReportsRequest($request);

        return view('tenant.teams.performance.show', [
            'team' => $team,
            'cards' => $metrics->forTeamMembers($team, $periodFilter),
            'periodFilter' => $periodFilter,
        ]);
    }
}
