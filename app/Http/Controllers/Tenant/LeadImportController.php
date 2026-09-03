<?php

namespace App\Http\Controllers\Tenant;

use App\Actions\CreateLead;
use App\Enums\LeadBudget;
use App\Enums\PropertyType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\ImportLeadsRequest;
use Illuminate\Http\RedirectResponse;

class LeadImportController extends Controller
{
    public function __invoke(ImportLeadsRequest $request, CreateLead $createLead): RedirectResponse
    {
        $file = $request->file('file');
        $handle = fopen($file->getRealPath(), 'r');

        if ($handle === false) {
            return redirect()
                ->route('tenant.leads.index')
                ->withErrors(['file' => __('Unable to read the uploaded file.')]);
        }

        $header = fgetcsv($handle);
        $imported = 0;
        $skipped = 0;

        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) < 1 || blank($row[0])) {
                $skipped++;

                continue;
            }

            $createLead->handle([
                'name' => $row[0],
                'phone' => $row[1] ?? null,
                'email' => $row[2] ?? null,
                'source' => $row[3] ?? null,
                'budget' => LeadBudget::tryFromMixed($row[4] ?? null)?->value,
                'location' => $row[5] ?? null,
                'property_type' => PropertyType::tryFromMixed($row[6] ?? null)?->value,
                'configuration' => $row[7] ?? null,
            ]);

            $imported++;
        }

        fclose($handle);

        return redirect()
            ->route('tenant.leads.index')
            ->with('status', __(':imported leads imported, :skipped skipped.', [
                'imported' => $imported,
                'skipped' => $skipped,
            ]));
    }
}
