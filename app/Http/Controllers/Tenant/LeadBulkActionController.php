<?php

namespace App\Http\Controllers\Tenant;

use App\Actions\LogLeadActivity;
use App\Enums\LeadActivityType;
use App\Enums\LeadListingFilter;
use App\Enums\LeadStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\BulkAssignLeadsRequest;
use App\Http\Requests\Tenant\BulkUpdateLeadStatusRequest;
use App\Models\Lead;
use Illuminate\Http\RedirectResponse;

class LeadBulkActionController extends Controller
{
    public function assign(BulkAssignLeadsRequest $request): RedirectResponse
    {
        $assignedToId = $request->validated('assigned_to_id');
        $updatedCount = 0;

        $leads = Lead::query()->whereIn('id', $request->leadIds())->get();

        foreach ($leads as $lead) {
            if ($lead->assigned_to_id === $assignedToId) {
                continue;
            }

            $lead->update(['assigned_to_id' => $assignedToId]);
            $updatedCount++;
        }

        $listing = LeadListingFilter::fromRequest($request->string('listing')->toString());

        return redirect()
            ->route('tenant.leads.index', $listing->redirectParameters($request->string('search')->trim()->toString()))
            ->with('status', trans_choice(
                ':count lead assigned successfully.|:count leads assigned successfully.',
                $updatedCount,
                ['count' => $updatedCount],
            ));
    }

    public function updateStatus(BulkUpdateLeadStatusRequest $request, LogLeadActivity $logLeadActivity): RedirectResponse
    {
        $newStatus = $request->enum('status', LeadStatus::class);
        $updatedCount = 0;

        $leads = Lead::query()->whereIn('id', $request->leadIds())->get();

        foreach ($leads as $lead) {
            $previousStatus = $lead->status;

            if ($previousStatus === $newStatus) {
                continue;
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

            $updatedCount++;
        }

        $listing = LeadListingFilter::fromRequest($request->string('listing')->toString());

        return redirect()
            ->route('tenant.leads.index', $listing->redirectParameters($request->string('search')->trim()->toString()))
            ->with('status', trans_choice(
                ':count lead status updated.|:count lead statuses updated.',
                $updatedCount,
                ['count' => $updatedCount],
            ));
    }
}
