<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Queries\DashboardPipeline;
use App\Support\DashboardPeriodFilter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardPipelineController extends Controller
{
    /**
     * Return sales pipeline chart data for the selected period.
     */
    public function __invoke(Request $request, DashboardPipeline $dashboardPipeline): JsonResponse
    {
        $periodFilter = DashboardPeriodFilter::fromRequest($request);
        $pipeline = $dashboardPipeline->forTenant($periodFilter);

        $maxCount = max((int) $pipeline['max_count'], 1);
        $tickStep = max(1, (int) ceil($maxCount / 5));
        $scaleMax = (int) (ceil($maxCount / $tickStep) * $tickStep);

        if ($scaleMax < $maxCount) {
            $scaleMax = $maxCount;
        }

        return response()->json([
            'period' => $periodFilter->period->value,
            'period_label' => $periodFilter->period->label(),
            'from' => $periodFilter->from,
            'to' => $periodFilter->to,
            'pipeline' => $pipeline,
            'scale_max' => $scaleMax,
            'ticks' => range(0, $scaleMax, $tickStep),
        ]);
    }
}
