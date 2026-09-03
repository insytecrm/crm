<?php

namespace App\Http\Controllers\Tenant;

use App\Actions\StorePropertyAttachments;
use App\Enums\ProjectStatus;
use App\Enums\PropertyType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\StorePropertyRequest;
use App\Http\Requests\Tenant\UpdatePropertyRequest;
use App\Models\Property;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PropertyController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->trim()->toString();

        $properties = Property::query()
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('project_name', 'like', "%{$search}%")
                        ->orWhere('developer_name', 'like', "%{$search}%")
                        ->orWhere('project_location', 'like', "%{$search}%")
                        ->orWhere('property_type', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('tenant.properties.index', [
            'properties' => $properties,
            'search' => $search,
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

    public function store(StorePropertyRequest $request, StorePropertyAttachments $storePropertyAttachments): RedirectResponse
    {
        $property = Property::query()->create([
            ...$request->safe()->except(['layout_files', 'brochure_files']),
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

    public function destroy(Property $property): RedirectResponse
    {
        $property->delete();

        return redirect()
            ->route('tenant.properties.index')
            ->with('status', __('Property deleted successfully.'));
    }
}
