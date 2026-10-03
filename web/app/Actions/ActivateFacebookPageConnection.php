<?php

namespace App\Actions;

use App\Enums\FacebookLeadMappableField;
use App\Enums\FacebookPageConnectionStatus;
use App\Models\FacebookPageConnection;
use App\Models\FacebookPageRegistration;
use InvalidArgumentException;

class ActivateFacebookPageConnection
{
    /**
     * @param  list<string>  $formIds
     * @param  array<string, string|null>  $fieldMap
     */
    public function handle(FacebookPageConnection $connection, array $formIds, array $fieldMap): FacebookPageConnection
    {
        if ($connection->status === FacebookPageConnectionStatus::Draft) {
            throw new InvalidArgumentException(__('Verify the Facebook Page before activating.'));
        }

        $availableFormIds = collect($connection->lead_forms ?? [])
            ->pluck('id')
            ->filter()
            ->values();

        $selected = collect($formIds)
            ->map(static fn (mixed $id): string => (string) $id)
            ->filter()
            ->unique()
            ->values();

        if ($selected->isEmpty()) {
            throw new InvalidArgumentException(__('Select at least one Lead Form to activate.'));
        }

        if ($selected->diff($availableFormIds)->isNotEmpty()) {
            throw new InvalidArgumentException(__('Choose Lead Forms from the verified Facebook Page.'));
        }

        $availableFields = collect($connection->form_fields ?? [])
            ->pluck('key')
            ->filter()
            ->values();

        $normalized = [];

        foreach (FacebookLeadMappableField::cases() as $field) {
            $key = $fieldMap[$field->value] ?? null;

            if (! is_string($key) || trim($key) === '') {
                if ($field->isRequired()) {
                    throw new InvalidArgumentException(__('Map the Name field before activating.'));
                }

                continue;
            }

            $key = trim($key);

            if (! $availableFields->contains($key)) {
                throw new InvalidArgumentException(__('Choose a field from the selected Lead Forms.'));
            }

            $normalized[$field->value] = $key;
        }

        $tenantId = tenant('id');

        if (! is_string($tenantId) || $tenantId === '') {
            throw new InvalidArgumentException(__('Facebook activation requires an active workspace.'));
        }

        FacebookPageRegistration::query()->updateOrCreate(
            ['page_id' => $connection->page_id],
            [
                'tenant_id' => $tenantId,
                'is_active' => true,
            ],
        );

        $connection->forceFill([
            'selected_form_ids' => $selected->all(),
            'field_map' => $normalized,
            'status' => FacebookPageConnectionStatus::Connected,
            'connected_at' => $connection->connected_at ?? now(),
            'last_error' => null,
        ])->save();

        return $connection->fresh();
    }
}
