<?php

namespace App\Contracts;

interface GoogleSheetsClient
{
    /**
     * @return array{title: string, sheets: list<array{title: string, sheetId: int}>}
     */
    public function spreadsheetMeta(string $spreadsheetId): array;

    /**
     * @return list<string>
     */
    public function headerRow(string $spreadsheetId, string $sheetTitle): array;

    /**
     * Fetch rows after `$afterRow` (1-indexed spreadsheet rows; row 1 is headers).
     *
     * @return list<array{row: int, values: list<string>}>
     */
    public function rowsAfter(string $spreadsheetId, string $sheetTitle, int $afterRow): array;
}
