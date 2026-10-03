<?php

namespace App\Actions;

use App\Contracts\GoogleSheetsClient;
use App\Enums\GoogleSheetConnectionStatus;
use App\Models\GoogleSheetConnection;
use RuntimeException;

class VerifyGoogleSheetConnection
{
    public function __construct(private GoogleSheetsClient $googleSheetsClient) {}

    public function handle(GoogleSheetConnection $connection): GoogleSheetConnection
    {
        try {
            $meta = $this->googleSheetsClient->spreadsheetMeta($connection->spreadsheet_id);
            $sheetTitle = $connection->sheet_title
                ?? ($meta['sheets'][0]['title'] ?? null);

            if (! is_string($sheetTitle) || $sheetTitle === '') {
                throw new RuntimeException(__('This spreadsheet has no sheets to read.'));
            }

            $headers = $this->googleSheetsClient->headerRow(
                $connection->spreadsheet_id,
                $sheetTitle,
            );

            $headers = array_values(array_filter(
                $headers,
                static fn (string $header): bool => $header !== '',
            ));

            if ($headers === []) {
                throw new RuntimeException(__('Add a header row to the first row of the sheet, then verify again.'));
            }

            $connection->forceFill([
                'sheet_title' => $sheetTitle,
                'headers' => $headers,
                'status' => GoogleSheetConnectionStatus::Verified,
                'verified_at' => now(),
                'last_error' => null,
                'column_map' => null,
                'connected_at' => null,
            ])->save();
        } catch (RuntimeException $exception) {
            $connection->forceFill([
                'last_error' => $exception->getMessage(),
                'status' => GoogleSheetConnectionStatus::Draft,
                'verified_at' => null,
                'connected_at' => null,
                'headers' => null,
                'column_map' => null,
            ])->save();

            throw $exception;
        }

        return $connection->fresh();
    }
}
