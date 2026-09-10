<?php

namespace App\Http\Controllers\Tenant;

use App\Actions\LogLeadActivity;
use App\Actions\RecalculateLeadScore;
use App\Enums\LeadActivityType;
use App\Enums\LeadStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\UpdateLeadStatusRequest;
use App\Models\Lead;
use App\Support\LeadDrawerRedirect;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class LeadStatusController extends Controller
{
    public function update(UpdateLeadStatusRequest $request, Lead $lead, LogLeadActivity $logLeadActivity, RecalculateLeadScore $recalculateLeadScore): JsonResponse|RedirectResponse
    {
        $previousStatus = $lead->status;
        $newStatus = $request->enum('status', LeadStatus::class);

        if ($previousStatus === $newStatus) {
            if ($this->wantsStatusJson($request)) {
                return response()->json([
                    'status' => $newStatus->value,
                    'label' => $newStatus->label(),
                ]);
            }

            return $request->boolean('redirect_to_listing')
                ? back()
                : LeadDrawerRedirect::to($lead);
        }

        $lead->update(['status' => $newStatus]);

        $logLeadActivity->handle(
            $lead,
            LeadActivityType::StatusChanged,
            __('Status changed from :from to :to', [
                'from' => $previousStatus->label(),
                'to' => $newStatus->label(),
            ]),
            metadata: [
                'from' => $previousStatus->value,
                'to' => $newStatus->value,
            ],
        );

        $recalculateLeadScore->handle($lead);

        if ($this->wantsStatusJson($request)) {
            return response()->json([
                'status' => $newStatus->value,
                'label' => $newStatus->label(),
                'message' => __('Lead status updated.'),
            ]);
        }

        if ($request->boolean('redirect_to_listing')) {
            return back()->with('status', __('Lead status updated.'));
        }

        return LeadDrawerRedirect::to($lead, __('Lead status updated.'));
    }

    private function wantsStatusJson(UpdateLeadStatusRequest $request): bool
    {
        return $request->ajax() || $request->wantsJson();
    }
}
