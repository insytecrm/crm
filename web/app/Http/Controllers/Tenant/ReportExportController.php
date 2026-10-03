<?php

namespace App\Http\Controllers\Tenant;

use App\Enums\TenantPermission;
use App\Http\Controllers\Controller;
use App\Queries\ReportsAnalytics;
use App\Support\DashboardPeriodFilter;
use App\Support\ReportExcelExport;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportExportController extends Controller
{
    public function __invoke(Request $request, ReportsAnalytics $reportsAnalytics): StreamedResponse
    {
        abort_unless($request->user()?->hasPermission(TenantPermission::ReportsView), 403);

        $periodFilter = DashboardPeriodFilter::fromReportsRequest($request);
        $analytics = $reportsAnalytics->forTenant($periodFilter);
        $export = new ReportExcelExport(
            analytics: $analytics,
            periodLabel: $periodFilter->period->label(),
            generatedAt: now()->toDateTimeString(),
        );

        $filename = 'reports-'.$periodFilter->period->value.'-'.now()->format('Y-m-d').'.xls';

        return response()->streamDownload(function () use ($export): void {
            echo $export->toSpreadsheetXml();
        }, $filename, [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
        ]);
    }
}
