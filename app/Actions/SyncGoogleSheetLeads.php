<?php

namespace App\Actions;

use App\Contracts\GoogleSheetsClient;
use App\Enums\GoogleSheetConnectionStatus;
use App\Enums\GoogleSheetMappableField;
use App\Enums\LeadBudget;
use App\Enums\LeadSource;
use App\Enums\PropertyType;
use App\Models\GoogleSheetConnection;
use App\Support\LeadSourcePath;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use RuntimeException;
use Throwable;

class SyncGoogleSheetLeads
{
    public function __construct(
        private GoogleSheetsClient $googleSheetsClient,
        private CreateLead $createLead,
    ) {}

    /**
     * @return array{created: int, skipped: int}
     */
    public function handle(GoogleSheetConnection $connection): array
    {
        if ($connection->status !== GoogleSheetConnectionStatus::Connected) {
            return ['created' => 0, 'skipped' => 0];
        }

        $sheetTitle = $connection->sheet_title;
        $columnMap = $connection->column_map ?? [];

        if (! is_string($sheetTitle) || $sheetTitle === '' || $columnMap === []) {
            return ['created' => 0, 'skipped' => 0];
        }

        $created = 0;
        $skipped = 0;
        $lastRow = $connection->last_synced_row;

        try {
            $rows = $this->googleSheetsClient->rowsAfter(
                $connection->spreadsheet_id,
                $sheetTitle,
                $connection->last_synced_row,
            );

            $headerIndex = $this->headerIndexes($connection->headers ?? [], $columnMap);

            foreach ($rows as $row) {
                $lastRow = max($lastRow, $row['row']);
                $payload = $this->mapRow($row['values'], $headerIndex, $connection->name);

                $validated = Validator::make($payload, [
                    'name' => ['required', 'string', 'max:255'],
                    'phone' => ['nullable', 'string', 'max:30'],
                    'email' => ['nullable', 'email', 'max:255'],
                    'source' => ['required', Rule::enum(LeadSource::class)],
                    'sub_source' => ['nullable', 'string', 'max:500'],
                    'source_context' => ['nullable', 'array'],
                    'budget' => ['nullable', Rule::enum(LeadBudget::class)],
                    'location' => ['nullable', 'string', 'max:255'],
                    'property_type' => ['nullable', Rule::enum(PropertyType::class)],
                    'configuration' => ['nullable', 'string', 'max:255'],
                ]);

                if ($validated->fails()) {
                    $skipped++;

                    continue;
                }

                $this->createLead->handle($validated->validated(), null);
                $created++;
            }

            $connection->forceFill([
                'last_synced_row' => $lastRow,
                'last_synced_at' => now(),
                'last_error' => null,
                'total_synced' => $connection->total_synced + $created,
                'total_skipped' => $connection->total_skipped + $skipped,
            ])->save();
        } catch (Throwable $exception) {
            $connection->forceFill([
                'last_error' => $exception instanceof RuntimeException
                    ? $exception->getMessage()
                    : __('Google Sheets sync failed.'),
                'total_failed' => $connection->total_failed + 1,
            ])->save();

            throw $exception;
        }

        return ['created' => $created, 'skipped' => $skipped];
    }

    /**
     * @param  list<string>  $headers
     * @param  array<string, string>  $columnMap
     * @return array<string, int>
     */
    private function headerIndexes(array $headers, array $columnMap): array
    {
        $indexes = [];

        foreach ($columnMap as $field => $header) {
            $index = array_search($header, $headers, true);

            if ($index === false) {
                continue;
            }

            $indexes[$field] = $index;
        }

        return $indexes;
    }

    /**
     * @param  list<string>  $values
     * @param  array<string, int>  $headerIndex
     * @return array<string, mixed>
     */
    private function mapRow(array $values, array $headerIndex, string $connectionName): array
    {
        $payload = [];
        $mappedSubSource = null;

        foreach (GoogleSheetMappableField::cases() as $field) {
            if (! array_key_exists($field->value, $headerIndex)) {
                continue;
            }

            $raw = $values[$headerIndex[$field->value]] ?? '';

            if ($raw === '') {
                continue;
            }

            if ($field === GoogleSheetMappableField::Source) {
                $mappedSubSource = $raw;

                continue;
            }

            $payload[$field->value] = match ($field) {
                GoogleSheetMappableField::Budget => LeadBudget::tryFromMixed($raw)?->value,
                GoogleSheetMappableField::PropertyType => PropertyType::tryFromMixed($raw)?->value,
                default => $raw,
            };
        }

        $payload['source'] = LeadSource::GoogleSheets->value;
        $path = LeadSourcePath::fromSegments([
            [
                'key' => 'connection',
                'id' => null,
                'label' => $mappedSubSource ?: $connectionName,
            ],
        ]);
        $payload['sub_source'] = $path['sub_source'];
        $payload['source_context'] = $path['source_context'];

        return array_filter(
            $payload,
            static fn (mixed $value): bool => $value !== null && $value !== '',
        );
    }
}
