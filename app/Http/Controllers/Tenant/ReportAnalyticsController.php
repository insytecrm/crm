<?php

namespace App\Http\Controllers\Tenant;

use App\Enums\TenantPermission;
use App\Http\Controllers\Controller;
use App\Models\SalesTeam;
use App\Models\User;
use App\Queries\AnalyticsMetrics;
use App\Support\DashboardPeriodFilter;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportAnalyticsController extends Controller
{
    public function __invoke(Request $request, AnalyticsMetrics $analyticsMetrics): View
    {
        abort_unless($request->user()?->hasPermission(TenantPermission::ReportsView), 403);

        $periodFilter = DashboardPeriodFilter::fromAnalyticsRequest($request);

        return view('tenant.reports.analytics', [
            'metrics' => $analyticsMetrics->forTenant($periodFilter),
            'periodFilter' => $periodFilter,
            'teams' => SalesTeam::query()->active()->orderBy('name')->get(['id', 'name']),
            'users' => User::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }
}
