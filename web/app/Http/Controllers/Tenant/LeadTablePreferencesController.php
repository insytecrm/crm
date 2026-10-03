<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\UpdateLeadTablePreferencesRequest;
use App\Support\LeadTablePreferences;
use Illuminate\Http\JsonResponse;

class LeadTablePreferencesController extends Controller
{
    public function update(UpdateLeadTablePreferencesRequest $request): JsonResponse
    {
        $user = $request->user();
        $listing = $request->listing();
        $preferences = $user->preferences ?? [];
        $savedByListing = LeadTablePreferences::normalizeSavedByListing(
            $preferences[LeadTablePreferences::StorageKey] ?? null,
        );

        $savedByListing[$listing->value] = LeadTablePreferences::normalize([
            'columns' => $request->validated('columns'),
            'actions' => $request->validated('actions'),
        ]);

        $preferences[LeadTablePreferences::StorageKey] = $savedByListing;

        $user->forceFill([
            'preferences' => $preferences,
        ])->save();

        return response()->json($user->leadTablePreferences($listing));
    }
}
