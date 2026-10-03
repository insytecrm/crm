<?php

namespace App\Http\Controllers\Tenant;

use App\Enums\TenantPermission;
use App\Http\Controllers\Controller;
use App\Queries\ReportsAnalytics;
use App\Support\DashboardPeriodFilter;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function __invoke(Request $request, ReportsAnalytics $reportsAnalytics): View
    {
        abort_unless($request->user()?->hasPermission(TenantPermission::ReportsView), 403);

        $periodFilter = DashboardPeriodFilter::fromReportsRequest($request);

        return view('tenant.reports.index', [
            'analytics' => $reportsAnalytics->forTenant($periodFilter),
            'periodFilter' => $periodFilter,
        ]);
    }
}
