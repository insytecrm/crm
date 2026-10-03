<?php

namespace App\Http\Controllers\Tenant;

use App\Actions\AssertPlanLimit;
use App\Actions\StorePropertyAttachments;
use App\Actions\UpdatePropertyMicrosite;
use App\Enums\PlanLimitKey;
use App\Enums\ProjectStatus;
use App\Enums\PropertyFilter;
use App\Enums\PropertyType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\StorePropertyRequest;
use App\Http\Requests\Tenant\UpdatePropertyMicrositeRequest;
use App\Http\Requests\Tenant\UpdatePropertyRequest;
use App\Http\Requests\Tenant\UpdatePropertyStatusRequest;
use App\Http\Requests\Tenant\UpdatePropertyWebsiteVisibilityRequest;
use App\Models\Property;
use App\Queries\PropertyListing;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PropertyController extends Controller
{
    public function index(Request $request, PropertyListing $propertyListing): View
    {
        $filter = PropertyFilter::fromRequest($request->string('filter')->toString());
        $search = $request->string('search')->trim()->toString();

        return view('tenant.properties.index', [
            'filter' => $filter,
            'properties' => $propertyListing->paginate($filter, $search),
            'search' => $search,
            'statistics' => $propertyListing->statistics(),
            'propertyTypes' => PropertyType::cases(),
            'projectStatuses' => ProjectStatus::cases(),
            'openEditPropertyId' => old('_edit_property_id'),
        ]);
    }

    public function create(): View
    {
        return view('tenant.properties.create', [
            'propertyTypes' => PropertyType::cases(),
            'projectStatuses' => ProjectStatus::cases(),
        ]);
    }

    public function store(StorePropertyRequest $request, StorePropertyAttachments $storePropertyAttachments, UpdatePropertyMicrosite $updatePropertyMicrosite): RedirectResponse
    {
        app(AssertPlanLimit::class)->handle(PlanLimitKey::Properties);

        $property = Property::query()->create([
            ...$request->safe()->except(['layout_files', 'brochure_files', 'microsite_enabled']),
            'created_by_id' => auth()->id(),
        ]);

        $attachments = $storePropertyAttachments->handle(
            $property,
            $request->file('layout_files'),
            $request->file('brochure_files'),
        );

        if ($attachments['layout_files'] !== [] || $attachments['brochure_files'] !== []) {
            $property->update($attachments);
        }

        if ($request->exists('microsite_enabled')) {
            $updatePropertyMicrosite->handle(
                $property,
                filter_var($request->input('microsite_enabled'), FILTER_VALIDATE_BOOLEAN),
            );
        }

        return redirect()
            ->route('tenant.properties.index')
            ->with('status', __('Property created successfully.'));
    }

    public function update(UpdatePropertyRequest $request, Property $property, StorePropertyAttachments $storePropertyAttachments): RedirectResponse
    {
        $property->update($request->safe()->except(['layout_files', 'brochure_files']));

        $attachments = $storePropertyAttachments->handle(
            $property,
            $request->file('layout_files'),
            $request->file('brochure_files'),
        );

        $updates = [];

        if ($attachments['layout_files'] !== []) {
            $updates['layout_files'] = array_merge($property->layout_files ?? [], $attachments['layout_files']);
        }

        if ($attachments['brochure_files'] !== []) {
            $updates['brochure_files'] = array_merge($property->brochure_files ?? [], $attachments['brochure_files']);
        }

        if ($updates !== []) {
            $property->update($updates);
        }

        return redirect()
            ->route('tenant.properties.index')
            ->with('status', __('Property updated successfully.'));
    }

    public function updateStatus(UpdatePropertyStatusRequest $request, Property $property): RedirectResponse
    {
        $property->update([
            'is_active' => $request->isActive(),
        ]);

        $message = $request->isActive()
            ? __('Property activated.')
            : __('Property deactivated.');

        return back()->with('status', $message);
    }

    public function updateWebsiteVisibility(UpdatePropertyWebsiteVisibilityRequest $request, Property $property): RedirectResponse
    {
        $property->update([
            'show_on_website' => $request->showOnWebsite(),
        ]);

        $message = $request->showOnWebsite()
            ? __('Property will show on website.')
            : __('Property hidden from website.');

        return back()->with('status', $message);
    }

    public function updateMicrosite(UpdatePropertyMicrositeRequest $request, Property $property, UpdatePropertyMicrosite $updatePropertyMicrosite): JsonResponse|RedirectResponse
    {
        $property = $updatePropertyMicrosite->handle($property, $request->micrositeEnabled());

        $message = $request->micrositeEnabled()
            ? __('Microsite enabled.')
            : __('Microsite disabled.');

        if ($request->ajax() || $request->wantsJson()) {
            $enabled = (bool) $property->microsite_enabled;

            return response()->json([
                'microsite_enabled' => $enabled,
                'microsite_url' => $enabled ? $property->micrositeUrl() : null,
                'message' => $message,
            ]);
        }

        return back()->with('status', $message);
    }

    public function destroy(Property $property): RedirectResponse
    {
        $property->delete();

        return redirect()
            ->route('tenant.properties.index')
            ->with('status', __('Property deleted successfully.'));
    }
}
