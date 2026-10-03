<?php

namespace App\Http\Controllers\Tenant;

use App\Enums\PropertyFilter;
use App\Enums\TenantPermission;
use App\Http\Controllers\Controller;
use App\Queries\PropertyListing;
use App\Support\PropertyCsvSchema;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PropertyExportController extends Controller
{
    public function __invoke(Request $request, PropertyListing $propertyListing): StreamedResponse
    {
        abort_unless(
            $request->user()?->hasPermission(TenantPermission::PropertiesView) ?? false,
            403,
        );

        $filter = PropertyFilter::fromRequest($request->string('filter')->toString());
        $search = $request->string('search')->trim()->toString();
        $filename = 'properties-'.now()->format('Y-m-d').'.csv';

        return response()->streamDownload(function () use ($propertyListing, $filter, $search): void {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, PropertyCsvSchema::headers());

            $propertyListing->exportCursor($filter, $search)->each(function ($property) use ($handle): void {
                fputcsv($handle, PropertyCsvSchema::mapExportRow($property));
            });

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }
}
