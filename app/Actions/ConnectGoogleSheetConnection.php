<?php

namespace App\Actions;

use App\Enums\GoogleSheetConnectionStatus;
use App\Enums\GoogleSheetMappableField;
use App\Models\GoogleSheetConnection;
use InvalidArgumentException;

class ConnectGoogleSheetConnection
{
    /**
     * @param  array<string, string|null>  $columnMap
     */
    public function handle(GoogleSheetConnection $connection, array $columnMap): GoogleSheetConnection
    {
        if ($connection->status === GoogleSheetConnectionStatus::Draft) {
            throw new InvalidArgumentException(__('Verify the Google Sheet before mapping columns.'));
        }

        $headers = collect($connection->headers ?? []);
        $normalized = [];

        foreach (GoogleSheetMappableField::cases() as $field) {
            $header = $columnMap[$field->value] ?? null;

            if (! is_string($header) || trim($header) === '') {
                if ($field->isRequired()) {
                    throw new InvalidArgumentException(__('Map the Name column before connecting.'));
                }

                continue;
            }

            $header = trim($header);

            if (! $headers->contains($header)) {
                throw new InvalidArgumentException(__('Choose a column from the verified sheet headers.'));
            }

            $normalized[$field->value] = $header;
        }

        $connection->forceFill([
            'column_map' => $normalized,
            'status' => GoogleSheetConnectionStatus::Connected,
            'connected_at' => $connection->connected_at ?? now(),
            'last_error' => null,
        ])->save();

        return $connection->fresh();
    }
}
