<?php

namespace App\Http\Controllers\Tenant;

use App\Actions\CloseLead;
use App\Enums\LeadClosingReason;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\CloseLeadRequest;
use App\Models\Lead;
use Illuminate\Http\RedirectResponse;

class CloseLeadController extends Controller
{
    public function store(CloseLeadRequest $request, Lead $lead, CloseLead $closeLead): RedirectResponse
    {
        $reason = $request->enum('closing_reason', LeadClosingReason::class);

        $closeLead->handle($lead, $reason);

        if ($reason === LeadClosingReason::Converted && $request->boolean('continue_to_booking')) {
            return redirect()
                ->route('tenant.bookings.index', ['lead' => $lead->id, 'create' => 1])
                ->with('status', __('Lead converted. Continue to create booking.'));
        }

        return redirect()
            ->route('tenant.leads.index')
            ->with('status', __('Lead closed successfully.'));
    }
}
