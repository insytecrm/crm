<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LeadExportController extends Controller
{
    public function __invoke(Request $request): StreamedResponse
    {
        $leadIds = collect($request->input('lead_ids', []))
            ->filter()
            ->map(fn ($id): int => (int) $id)
            ->unique()
            ->values()
            ->all();

        $filename = 'leads-'.now()->format('Y-m-d').'.csv';

        return response()->streamDownload(function () use ($leadIds): void {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'Name',
                'Phone',
                'Email',
                'Source',
                'Budget',
                'Location',
                'Property Type',
                'Configuration',
                'Status',
                'Assigned To',
                'Next Follow-up',
                'Created At',
            ]);

            $query = Lead::query()
                ->with('assignedTo')
                ->orderBy('id');

            if ($leadIds !== []) {
                $query->whereIn('id', $leadIds);
            }

            $query->chunk(100, function ($leads) use ($handle): void {
                foreach ($leads as $lead) {
                    fputcsv($handle, [
                        $lead->name,
                        $lead->phone,
                        $lead->email,
                        $lead->source,
                        $lead->budget?->label(),
                        $lead->location,
                        $lead->property_type?->label(),
                        $lead->configuration,
                        $lead->status->label(),
                        $lead->assignedTo?->name,
                        $lead->next_follow_up_at?->toDateTimeString(),
                        $lead->created_at?->toDateTimeString(),
                    ]);
                }
            });

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }
}
