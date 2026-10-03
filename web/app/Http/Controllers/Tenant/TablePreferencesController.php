<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\UpdateTablePreferencesRequest;
use App\Support\DataTable\DataTableRegistry;
use App\Support\DataTable\TablePreferencesSupport;
use Illuminate\Http\JsonResponse;

class TablePreferencesController extends Controller
{
    public function update(UpdateTablePreferencesRequest $request, string $tableKey): JsonResponse
    {
        $definition = DataTableRegistry::get($tableKey);
        $user = $request->user();
        $preferences = $user->preferences ?? [];
        $storageKey = TablePreferencesSupport::storageKey($tableKey);
        $listingKey = $request->listingKey();

        if ($listingKey !== null) {
            $savedByListing = TablePreferencesSupport::normalizeSavedByListing(
                $definition,
                $preferences[$storageKey] ?? null,
            );

            $savedByListing[$listingKey] = TablePreferencesSupport::normalize(
                $definition,
                $request->validated(),
            );

            $preferences[$storageKey] = $savedByListing;
        } else {
            $preferences[$storageKey] = TablePreferencesSupport::normalize(
                $definition,
                $request->validated(),
            );
        }

        $user->preferences = $preferences;
        $user->save();

        return response()->json(
            $user->dataTablePreferences($tableKey, $definition, $listingKey),
        );
    }
}
