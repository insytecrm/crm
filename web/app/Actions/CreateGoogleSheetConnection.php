<?php

namespace App\Actions;

use App\Enums\GoogleSheetConnectionStatus;
use App\Models\GoogleSheetConnection;
use App\Models\User;

class CreateGoogleSheetConnection
{
    public function __construct(private ParseGoogleSheetUrl $parseGoogleSheetUrl) {}

    /**
     * @param  array{name: string, spreadsheet_url: string}  $data
     */
    public function handle(array $data, ?User $user = null): GoogleSheetConnection
    {
        $user ??= auth()->user();
        $parsed = $this->parseGoogleSheetUrl->handle($data['spreadsheet_url']);

        return GoogleSheetConnection::query()->create([
            'created_by_id' => $user?->id,
            'name' => $data['name'],
            'spreadsheet_url' => trim($data['spreadsheet_url']),
            'spreadsheet_id' => $parsed['spreadsheet_id'],
            'status' => GoogleSheetConnectionStatus::Draft,
            'last_synced_row' => 1,
        ]);
    }
}
