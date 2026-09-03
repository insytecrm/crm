<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Queries\DashboardKpis;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the tenant dashboard.
     */
    public function __invoke(DashboardKpis $dashboardKpis): View
    {
        return view('tenant.dashboard', [
            'kpis' => $dashboardKpis->forTenant(),
        ]);
    }
}
