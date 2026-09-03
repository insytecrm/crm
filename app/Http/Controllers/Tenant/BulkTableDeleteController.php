<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\BulkTableDeleteRequest;
use App\Support\DataTable\DataTableRegistry;
use Illuminate\Http\RedirectResponse;

class BulkTableDeleteController extends Controller
{
    public function destroy(BulkTableDeleteRequest $request, string $tableKey): RedirectResponse
    {
        $definition = DataTableRegistry::get($tableKey);
        $parameter = $definition->bulkDeleteParameterName();
        $ids = $request->validated($parameter);
        $deleted = $definition->bulkDelete($ids);

        $message = $deleted === 1
            ? __('1 item deleted.')
            : __(':count items deleted.', ['count' => $deleted]);

        return redirect()
            ->back()
            ->with('status', $message);
    }
}
