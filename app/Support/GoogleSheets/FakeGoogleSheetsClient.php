<?php

namespace App\Support\GoogleSheets;

use App\Contracts\GoogleSheetsClient;
use RuntimeException;

class FakeGoogleSheetsClient implements GoogleSheetsClient
{
    /**
     * @param  array<string, array{title?: string, sheets?: list<array{title: string, sheetId: int}>, headers?: list<string>, rows?: list<list<string>>}>  $spreadsheets
     */
    public function __construct(private array $spreadsheets = []) {}

    /**
     * @param  list<string>  $headers
     * @param  list<list<string>>  $rows
     * @param  list<array{title: string, sheetId: int}>  $sheets
     */
    public function seed(
        string $spreadsheetId,
        array $headers,
        array $rows = [],
        string $title = 'Leads',
        array $sheets = [['title' => 'Sheet1', 'sheetId' => 0]],
    ): self {
        $this->spreadsheets[$spreadsheetId] = [
            'title' => $title,
            'sheets' => $sheets,
            'headers' => $headers,
            'rows' => $rows,
        ];

        return $this;
    }

    public function fail(string $spreadsheetId, string $message = 'Sheet unavailable'): self
    {
        $this->spreadsheets[$spreadsheetId] = [
            'error' => $message,
        ];

        return $this;
    }

    public function spreadsheetMeta(string $spreadsheetId): array
    {
        $sheet = $this->requireSheet($spreadsheetId);

        return [
            'title' => $sheet['title'] ?? 'Leads',
            'sheets' => $sheet['sheets'] ?? [['title' => 'Sheet1', 'sheetId' => 0]],
        ];
    }

    public function headerRow(string $spreadsheetId, string $sheetTitle): array
    {
        $sheet = $this->requireSheet($spreadsheetId);

        return array_values($sheet['headers'] ?? []);
    }

    public function rowsAfter(string $spreadsheetId, string $sheetTitle, int $afterRow): array
    {
        $sheet = $this->requireSheet($spreadsheetId);
        $rows = $sheet['rows'] ?? [];
        $result = [];

        foreach ($rows as $index => $values) {
            $rowNumber = $index + 2;

            if ($rowNumber <= $afterRow) {
                continue;
            }

            $result[] = [
                'row' => $rowNumber,
                'values' => array_values($values),
            ];
        }

        return $result;
    }

    /**
     * @return array{title?: string, sheets?: list<array{title: string, sheetId: int}>, headers?: list<string>, rows?: list<list<string>>, error?: string}
     */
    private function requireSheet(string $spreadsheetId): array
    {
        if (! array_key_exists($spreadsheetId, $this->spreadsheets)) {
            throw new RuntimeException(__('Google Sheet not found. Check the URL and try again.'));
        }

        $sheet = $this->spreadsheets[$spreadsheetId];

        if (isset($sheet['error'])) {
            throw new RuntimeException((string) $sheet['error']);
        }

        return $sheet;
    }
}
