<?php

namespace Database\Factories;

use App\Enums\GoogleSheetConnectionStatus;
use App\Models\GoogleSheetConnection;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GoogleSheetConnection>
 */
class GoogleSheetConnectionFactory extends Factory
{
    protected $model = GoogleSheetConnection::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $spreadsheetId = '1'.fake()->regexify('[A-Za-z0-9_-]{40}');

        return [
            'created_by_id' => User::factory(),
            'name' => fake()->words(3, true),
            'spreadsheet_url' => "https://docs.google.com/spreadsheets/d/{$spreadsheetId}/edit",
            'spreadsheet_id' => $spreadsheetId,
            'sheet_title' => null,
            'status' => GoogleSheetConnectionStatus::Draft,
            'headers' => null,
            'column_map' => null,
            'last_synced_row' => 1,
            'last_synced_at' => null,
            'total_synced' => 0,
            'total_skipped' => 0,
            'total_failed' => 0,
            'verified_at' => null,
            'connected_at' => null,
            'last_error' => null,
        ];
    }

    public function verified(array $headers = ['Name', 'Phone', 'Email']): static
    {
        return $this->state(fn (): array => [
            'status' => GoogleSheetConnectionStatus::Verified,
            'sheet_title' => 'Sheet1',
            'headers' => $headers,
            'verified_at' => now(),
        ]);
    }

    /**
     * @param  array<string, string>  $columnMap
     */
    public function connected(
        array $headers = ['Name', 'Phone', 'Email'],
        array $columnMap = ['name' => 'Name', 'phone' => 'Phone', 'email' => 'Email'],
    ): static {
        return $this->state(fn (): array => [
            'status' => GoogleSheetConnectionStatus::Connected,
            'sheet_title' => 'Sheet1',
            'headers' => $headers,
            'column_map' => $columnMap,
            'verified_at' => now(),
            'connected_at' => now(),
        ]);
    }

    public function paused(): static
    {
        return $this->connected()->state(fn (): array => [
            'status' => GoogleSheetConnectionStatus::Paused,
        ]);
    }
}
