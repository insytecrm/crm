<?php

namespace App\Http\Controllers\Tenant;

use App\Actions\AssertPlanLimit;
use App\Actions\ImportPropertyFromCsvRow;
use App\Enums\PlanLimitKey;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\ImportPropertiesRequest;
use App\Support\Platform\TenantPlanAccess;
use Illuminate\Http\RedirectResponse;

class PropertyImportController extends Controller
{
    public function __invoke(
        ImportPropertiesRequest $request,
        ImportPropertyFromCsvRow $importPropertyFromCsvRow,
        TenantPlanAccess $tenantPlanAccess,
    ): RedirectResponse {
        $file = $request->file('file');
        $handle = fopen($file->getRealPath(), 'r');

        if ($handle === false) {
            return redirect()
                ->route('tenant.properties.index')
                ->withErrors(['file' => __('Unable to read the uploaded file.')]);
        }

        fgetcsv($handle);

        $imported = 0;
        $skipped = 0;
        $limitReached = false;

        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) < 1 || blank($row[0])) {
                $skipped++;

                continue;
            }

            if (! $tenantPlanAccess->canConsume(PlanLimitKey::Properties)) {
                $limitReached = true;
                $skipped++;

                continue;
            }

            app(AssertPlanLimit::class)->handle(PlanLimitKey::Properties);

            $importPropertyFromCsvRow->handle($row, (int) auth()->id());
            $imported++;
        }

        fclose($handle);

        $status = __(':imported properties imported, :skipped skipped.', [
            'imported' => $imported,
            'skipped' => $skipped,
        ]);

        if ($limitReached) {
            $status .= ' '.__('Property limit reached. Remaining rows were skipped.');
        }

        return redirect()
            ->route('tenant.properties.index')
            ->with('status', $status);
    }
}
