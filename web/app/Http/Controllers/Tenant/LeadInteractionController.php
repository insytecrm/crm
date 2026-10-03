<?php

namespace App\Http\Controllers\Tenant;

use App\Actions\LogLeadInteraction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\StoreLeadInteractionRequest;
use App\Models\Lead;
use App\Support\LeadDrawerRedirect;
use Illuminate\Http\RedirectResponse;

class LeadInteractionController extends Controller
{
    public function store(
        StoreLeadInteractionRequest $request,
        Lead $lead,
        LogLeadInteraction $logLeadInteraction,
    ): RedirectResponse {
        $logLeadInteraction->handle($lead, $request->validated());

        return LeadDrawerRedirect::to($lead, __('Interaction logged.'));
    }
}
