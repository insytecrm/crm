<?php

namespace App\Http\Controllers\Tenant;

use App\Actions\LogLeadActivity;
use App\Enums\LeadActivityType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\StoreLeadActivityRequest;
use App\Models\Lead;
use App\Support\LeadDrawerRedirect;
use Illuminate\Http\RedirectResponse;

class LeadActivityController extends Controller
{
    public function store(StoreLeadActivityRequest $request, Lead $lead, LogLeadActivity $logLeadActivity): RedirectResponse
    {
        $type = $request->enum('type', LeadActivityType::class);
        $description = $request->validated('description') ?: $type->label();

        $logLeadActivity->handle($lead, $type, $description);

        if ($request->filled('redirect_url')) {
            return LeadDrawerRedirect::to($lead, __('Activity logged.'))
                ->with('external_redirect', $request->string('redirect_url')->toString());
        }

        return LeadDrawerRedirect::to($lead, __('Activity logged.'));
    }
}
