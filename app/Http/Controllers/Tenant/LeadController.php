<?php

namespace App\Http\Controllers\Tenant;

use App\Actions\CreateLead;
use App\Actions\RecalculateLeadScore;
use App\Enums\LeadListingFilter;
use App\Enums\LeadSource;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\BulkDeleteLeadsRequest;
use App\Http\Requests\Tenant\StoreLeadRequest;
use App\Http\Requests\Tenant\UpdateLeadRequest;
use App\Models\Lead;
use App\Models\Property;
use App\Models\User;
use App\Queries\LeadListing;
use App\Queries\LeadStatistics;
use App\Support\LeadDrawerRedirect;
use App\Support\LeadListFilters;
use App\Support\LeadTablePreferences;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LeadController extends Controller
{
    public function index(Request $request, LeadListing $leadListing, LeadStatistics $leadStatistics): View
    {
        $listing = LeadListingFilter::fromRequest($request->string('filter')->toString());

        return $this->renderListing($request, $leadListing, $leadStatistics, $listing);
    }

    public function priority(Request $request, LeadListing $leadListing, LeadStatistics $leadStatistics): View
    {
        return $this->renderListing($request, $leadListing, $leadStatistics, LeadListingFilter::Priority);
    }

    public function unassigned(Request $request, LeadListing $leadListing, LeadStatistics $leadStatistics): View
    {
        return $this->renderListing($request, $leadListing, $leadStatistics, LeadListingFilter::Unassigned);
    }

    public function converted(Request $request, LeadListing $leadListing, LeadStatistics $leadStatistics): View
    {
        return $this->renderListing($request, $leadListing, $leadStatistics, LeadListingFilter::Converted);
    }

    public function lost(Request $request, LeadListing $leadListing, LeadStatistics $leadStatistics): View
    {
        return $this->renderListing($request, $leadListing, $leadStatistics, LeadListingFilter::Lost);
    }

    public function store(StoreLeadRequest $request, CreateLead $createLead): RedirectResponse
    {
        $createLead->handle($request->validated());

        return redirect()
            ->route('tenant.leads.index')
            ->with('status', __('Lead created successfully.'));
    }

    public function show(Request $request, Lead $lead): View|RedirectResponse
    {
        $lead = $this->loadLeadRelations($lead);

        if ($request->ajax()) {
            return view('tenant.leads.partials.drawer-response', [
                'lead' => $lead,
                'users' => User::query()->orderBy('name')->get(),
                'bookingProperties' => Property::bookingFormOptions(),
            ]);
        }

        return LeadDrawerRedirect::to($lead);
    }

    public function update(UpdateLeadRequest $request, Lead $lead, RecalculateLeadScore $recalculateLeadScore): RedirectResponse
    {
        $data = $request->validated();
        $source = LeadSource::tryFrom($data['source'] ?? '');

        if ($source?->isManual()) {
            $data['sub_source'] = null;
            $data['source_context'] = null;
        }

        $lead->update($data);
        $recalculateLeadScore->handle($lead);

        return LeadDrawerRedirect::to($lead, __('Lead updated successfully.'));
    }

    public function bulkDestroy(BulkDeleteLeadsRequest $request): RedirectResponse
    {
        $leadIds = $request->validated('lead_ids');
        $listing = LeadListingFilter::fromRequest($request->string('listing')->toString());

        $deletedCount = Lead::query()
            ->whereIn('id', $leadIds)
            ->delete();

        $listFilters = LeadListFilters::fromRequest($request);

        return redirect()
            ->route('tenant.leads.index', $listing->redirectParameters(
                $request->string('search')->trim()->toString(),
                $listFilters->toQueryArray(),
            ))
            ->with('status', trans_choice(':count lead deleted successfully.|:count leads deleted successfully.', $deletedCount, ['count' => $deletedCount]));
    }

    private function renderListing(
        Request $request,
        LeadListing $leadListing,
        LeadStatistics $leadStatistics,
        LeadListingFilter $listing,
    ): View {
        $search = $request->string('search')->trim()->toString();
        $listFilters = LeadListFilters::fromRequest($request);

        return view('tenant.leads.index', [
            'listing' => $listing,
            'leads' => $leadListing->paginate($listing, $search, $listFilters),
            'statistics' => $leadStatistics->forTenant(),
            'search' => $search,
            'listFilters' => $listFilters,
            'sources' => LeadSource::filterCases(),
            'users' => User::query()->orderBy('name')->get(),
            'bookingProperties' => Property::bookingFormOptions(),
            'leadTablePreferences' => $request->user()->leadTablePreferences($listing),
            'leadTableDefaults' => LeadTablePreferences::defaultsForListing($listing),
            'leadListingKey' => $listing->value,
            'openModal' => old('_open_modal') ?? ($listing === LeadListingFilter::All && $request->boolean('add') ? 'add-lead' : null),
        ]);
    }

    private function loadLeadRelations(Lead $lead): Lead
    {
        return $lead->load([
            'assignedTo',
            'createdBy',
            'activities.user',
            'tasks.assignedTo',
            'notes.user',
            'documents.uploadedBy',
            'completedSiteVisitEvents.property',
            'scheduledEvents.property',
            'latestBooking.property',
        ])->loadCount('bookings');
    }
}
