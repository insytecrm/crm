<?php

namespace App\Http\Controllers\Tenant;

use App\Enums\TenantPermission;
use App\Http\Controllers\Controller;
use App\Support\PropertyCsvSchema;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PropertyImportSampleController extends Controller
{
    public function __invoke(Request $request): StreamedResponse
    {
        abort_unless(
            $request->user()?->hasPermission(TenantPermission::PropertiesManage) ?? false,
            403,
        );

        return response()->streamDownload(function (): void {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, PropertyCsvSchema::headers());
            fputcsv($handle, PropertyCsvSchema::sampleRow());

            fclose($handle);
        }, 'properties-import-sample.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }
}
