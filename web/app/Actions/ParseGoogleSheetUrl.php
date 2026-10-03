<?php

namespace App\Actions;

use InvalidArgumentException;

class ParseGoogleSheetUrl
{
    /**
     * @return array{spreadsheet_id: string}
     */
    public function handle(string $url): array
    {
        $url = trim($url);

        if ($url === '') {
            throw new InvalidArgumentException(__('Enter a Google Sheet URL.'));
        }

        if (preg_match('#/spreadsheets/d/([a-zA-Z0-9-_]+)#', $url, $matches) === 1) {
            return ['spreadsheet_id' => $matches[1]];
        }

        if (preg_match('#^[a-zA-Z0-9-_]{20,}$#', $url) === 1) {
            return ['spreadsheet_id' => $url];
        }

        throw new InvalidArgumentException(__('Enter a valid Google Sheets URL.'));
    }
}
