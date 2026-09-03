<?php

namespace App\Http\Controllers\Tenant;

use App\Enums\PropertyType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\UpdateLeadPropertyTypeRequest;
use App\Models\Lead;
use App\Support\LeadDrawerRedirect;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class LeadPropertyTypeController extends Controller
{
    public function update(UpdateLeadPropertyTypeRequest $request, Lead $lead): JsonResponse|RedirectResponse
    {
        $newPropertyType = $request->enum('property_type', PropertyType::class);
        $previousPropertyType = $lead->property_type;

        if ($previousPropertyType === $newPropertyType) {
            if ($this->wantsJson($request)) {
                return response()->json($this->propertyTypePayload($newPropertyType));
            }

            return $request->boolean('redirect_to_listing')
                ? back()
                : LeadDrawerRedirect::to($lead);
        }

        $lead->update(['property_type' => $newPropertyType]);

        if ($this->wantsJson($request)) {
            return response()->json([
                ...$this->propertyTypePayload($newPropertyType),
                'message' => __('Lead property type updated.'),
            ]);
        }

        if ($request->boolean('redirect_to_listing')) {
            return back()->with('status', __('Lead property type updated.'));
        }

        return LeadDrawerRedirect::to($lead, __('Lead property type updated.'));
    }

    /**
     * @return array{property_type: ?string, label: string}
     */
    private function propertyTypePayload(?PropertyType $propertyType): array
    {
        return [
            'property_type' => $propertyType?->value,
            'label' => $propertyType?->label() ?? '—',
        ];
    }

    private function wantsJson(UpdateLeadPropertyTypeRequest $request): bool
    {
        return $request->ajax() || $request->wantsJson();
    }
}
