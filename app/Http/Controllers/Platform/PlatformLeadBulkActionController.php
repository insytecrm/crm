<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Http\Requests\Platform\BulkPlatformLeadActionRequest;
use App\Models\PlatformLead;
use Illuminate\Http\RedirectResponse;

class PlatformLeadBulkActionController extends Controller
{
    public function destroy(BulkPlatformLeadActionRequest $request): RedirectResponse
    {
        $deleted = PlatformLead::query()
            ->whereIn('id', $request->validated('ids'))
            ->delete();

        return redirect()
            ->route('platform.leads', $request->input('_redirect_query', []))
            ->with('status', trans_choice('Deleted :count lead.|Deleted :count leads.', $deleted, ['count' => $deleted]));
    }
}
