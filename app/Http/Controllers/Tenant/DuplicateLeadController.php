<?php

namespace App\Http\Controllers\Tenant;

use App\Actions\MergeLeads;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\MergeLeadsRequest;
use App\Queries\DuplicateLeadGroups;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class DuplicateLeadController extends Controller
{
    public function index(DuplicateLeadGroups $duplicateLeadGroups): View
    {
        $groups = $duplicateLeadGroups->all();

        return view('tenant.leads.duplicates.index', [
            'groups' => $groups,
            'duplicateGroupCount' => $groups->count(),
            'duplicateLeadCount' => $groups->sum(fn (array $group): int => $group['leads']->count()),
        ]);
    }

    public function merge(MergeLeadsRequest $request, MergeLeads $mergeLeads): RedirectResponse
    {
        $validated = $request->validated();

        $mergeLeads->handle(
            primaryLead: $request->primaryLead(),
            duplicateLeadIds: $validated['duplicate_lead_ids'],
        );

        return redirect()
            ->route('tenant.leads.duplicates.index')
            ->with('status', __('Duplicate leads merged successfully.'));
    }
}
