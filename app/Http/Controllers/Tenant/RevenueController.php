<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Queries\RevenueDashboard;
use App\Support\RevenueFilter;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RevenueController extends Controller
{
    public function __invoke(Request $request, RevenueDashboard $revenueDashboard): View
    {
        $filter = RevenueFilter::fromRequest($request);
        $filterOptions = $revenueDashboard->filterOptions();
        $topSalespeople = $revenueDashboard->topSalespeople($filter);

        return view('tenant.revenue.index', [
            'filter' => $filter,
            'filterOptions' => $filterOptions,
            'summary' => $revenueDashboard->summary($filter),
            'trend' => $revenueDashboard->trend($filter),
            'projects' => $revenueDashboard->byProject($filter),
            'topSalespeople' => $topSalespeople,
            'salespeopleMaxValues' => [
                'revenue' => max($topSalespeople->max('revenue') ?? 0, 1),
                'bookings' => max($topSalespeople->max('bookings') ?? 0, 1),
                'sales_value' => max($topSalespeople->max('sales_value') ?? 0, 1),
            ],
        ]);
    }
}
