<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Support\Platform\BillingOverviewData;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class RevenueOverviewController extends Controller
{
    public function __invoke(Request $request, BillingOverviewData $overview): View
    {
        return view('platform.revenue.overview', [
            'overview' => $overview->forRequest($request),
        ]);
    }

    public function export(Request $request, BillingOverviewData $overview): StreamedResponse
    {
        $data = $overview->forRequest($request);
        $filename = 'revenue-overview-'.$data['period']['start'].'-to-'.$data['period']['end'].'.csv';

        return response()->streamDownload(function () use ($data): void {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['Metric', 'Value']);
            fputcsv($handle, ['Period', $data['period']['label']]);
            fputcsv($handle, ['Total Revenue', $data['snapshot']['total_revenue']]);
            fputcsv($handle, ['Collected', $data['snapshot']['collected']]);
            fputcsv($handle, ['Pending', $data['snapshot']['pending']]);
            fputcsv($handle, ['Overdue', $data['snapshot']['overdue']]);
            fputcsv($handle, []);
            fputcsv($handle, ['Plan', 'Active Partners', 'Revenue']);

            foreach ($data['by_plan'] as $row) {
                fputcsv($handle, [$row['plan'], $row['partners'], $row['revenue']]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }
}
