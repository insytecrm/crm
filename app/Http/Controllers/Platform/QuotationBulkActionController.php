<?php

namespace App\Http\Controllers\Platform;

use App\Enums\QuotationStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Platform\BulkQuotationActionRequest;
use App\Models\Quotation;
use Illuminate\Http\RedirectResponse;

class QuotationBulkActionController extends Controller
{
    public function destroy(BulkQuotationActionRequest $request): RedirectResponse
    {
        $deleted = Quotation::query()
            ->whereIn('id', $request->validated('ids'))
            ->where('status', QuotationStatus::Draft)
            ->delete();

        return redirect()
            ->route('platform.quotations', $request->input('_redirect_query', []))
            ->with('status', trans_choice('Deleted :count draft quotation.|Deleted :count draft quotations.', $deleted, ['count' => $deleted]));
    }
}
