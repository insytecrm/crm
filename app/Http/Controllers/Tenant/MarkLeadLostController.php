<?php

namespace App\Http\Controllers\Tenant;

use App\Actions\MarkLeadLost;
use App\Enums\LeadLostReason;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\MarkLeadLostRequest;
use App\Models\Lead;
use App\Support\LeadDrawerRedirect;
use Illuminate\Http\RedirectResponse;

class MarkLeadLostController extends Controller
{
    public function store(MarkLeadLostRequest $request, Lead $lead, MarkLeadLost $markLeadLost): RedirectResponse
    {
        $lostReasons = collect($request->input('lost_reasons', []))
            ->map(fn (string $value): LeadLostReason => LeadLostReason::from($value))
            ->all();

        $markLeadLost->handle($lead, $lostReasons, $request->string('closing_notes')->toString());

        if ($request->boolean('redirect_to_listing')) {
            return back()->with('status', __('Lead marked as lost.'));
        }

        return LeadDrawerRedirect::to($lead, __('Lead marked as lost.'));
    }
}
