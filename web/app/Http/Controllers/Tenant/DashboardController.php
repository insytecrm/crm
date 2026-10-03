<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Queries\DashboardKpis;
use App\Queries\DashboardMyDay;
use App\Queries\DashboardPipeline;
use App\Queries\DashboardTodaysTasks;
use App\Queries\TenantSetupProgress;
use App\Support\DashboardPeriodFilter;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the tenant dashboard.
     */
    public function __invoke(
        Request $request,
        DashboardKpis $dashboardKpis,
        DashboardPipeline $dashboardPipeline,
        DashboardMyDay $dashboardMyDay,
        DashboardTodaysTasks $dashboardTodaysTasks,
        TenantSetupProgress $tenantSetupProgress,
    ): View {
        $periodFilter = DashboardPeriodFilter::fromRequest($request);

        return view('tenant.dashboard', [
            'kpis' => $dashboardKpis->forTenant(),
            'periodFilter' => $periodFilter,
            'pipeline' => $dashboardPipeline->forTenant($periodFilter),
            'myDayActivities' => $dashboardMyDay->forTenant(),
            'todaysTasks' => $dashboardTodaysTasks->forTenant(),
            'setupProgress' => $tenantSetupProgress->forUser($request->user()),
        ]);
    }
}
