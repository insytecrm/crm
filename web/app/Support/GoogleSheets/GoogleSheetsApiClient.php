<?php

namespace App\Support\GoogleSheets;

use App\Contracts\GoogleSheetsClient;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class GoogleSheetsApiClient implements GoogleSheetsClient
{
    public function spreadsheetMeta(string $spreadsheetId): array
    {
        try {
            $response = $this->client()
                ->get("https://sheets.googleapis.com/v4/spreadsheets/{$spreadsheetId}", [
                    'fields' => 'properties.title,sheets.properties(sheetId,title)',
                ])
                ->throw();
        } catch (RequestException $exception) {
            throw new RuntimeException(
                $this->friendlyError($exception),
                previous: $exception,
            );
        } catch (ConnectionException $exception) {
            throw new RuntimeException(
                __('Could not reach Google Sheets. Try again in a moment.'),
                previous: $exception,
            );
        }

        $sheets = collect($response->json('sheets', []))
            ->map(fn (array $sheet): array => [
                'title' => (string) data_get($sheet, 'properties.title', ''),
                'sheetId' => (int) data_get($sheet, 'properties.sheetId', 0),
            ])
            ->filter(fn (array $sheet): bool => $sheet['title'] !== '')
            ->values()
            ->all();

        return [
            'title' => (string) $response->json('properties.title', ''),
            'sheets' => $sheets,
        ];
    }

    public function headerRow(string $spreadsheetId, string $sheetTitle): array
    {
        $values = $this->values($spreadsheetId, $this->range($sheetTitle, 'A1:ZZ'));
        $row = $values[0] ?? [];

        return array_values(array_map(
            static fn (mixed $cell): string => trim((string) $cell),
            $row,
        ));
    }

    public function rowsAfter(string $spreadsheetId, string $sheetTitle, int $afterRow): array
    {
        $values = $this->values($spreadsheetId, $this->range($sheetTitle, 'A1:ZZ'));
        $skipThroughRow = max($afterRow, 1);
        $rows = [];

        foreach ($values as $offset => $row) {
            if (! is_array($row)) {
                continue;
            }

            $rowNumber = $offset + 1;

            if ($rowNumber <= $skipThroughRow) {
                continue;
            }

            $normalized = array_values(array_map(
                static fn (mixed $cell): string => trim((string) $cell),
                $row,
            ));

            if ($this->rowIsEmpty($normalized)) {
                continue;
            }

            $rows[] = [
                'row' => $rowNumber,
                'values' => $normalized,
            ];
        }

        return $rows;
    }

    /**
     * @return list<list<mixed>>
     */
    private function values(string $spreadsheetId, string $range): array
    {
        $encodedRange = rawurlencode($range);

        try {
            $response = $this->client()
                ->get("https://sheets.googleapis.com/v4/spreadsheets/{$spreadsheetId}/values/{$encodedRange}")
                ->throw();
        } catch (RequestException $exception) {
            throw new RuntimeException(
                $this->friendlyError($exception),
                previous: $exception,
            );
        } catch (ConnectionException $exception) {
            throw new RuntimeException(
                __('Could not reach Google Sheets. Try again in a moment.'),
                previous: $exception,
            );
        }

        /** @var list<list<mixed>> $values */
        $values = $response->json('values', []);

        return $values;
    }

    private function client(): PendingRequest
    {
        $apiKey = config('services.google_sheets.api_key');

        if (! is_string($apiKey) || $apiKey === '') {
            throw new RuntimeException(__('Google Sheets API key is not configured.'));
        }

        return Http::acceptJson()
            ->connectTimeout(3)
            ->timeout(15)
            ->retry([100, 500, 1000], 0, function (\Throwable $exception): bool {
                return $exception instanceof ConnectionException
                    || ($exception instanceof RequestException
                        && ($exception->response->serverError() || $exception->response->status() === 429));
            })
            ->withQueryParameters([
                'key' => $apiKey,
            ]);
    }

    private function range(string $sheetTitle, string $rows): string
    {
        $escapedTitle = str_replace("'", "''", $sheetTitle);

        return "'{$escapedTitle}'!{$rows}";
    }

    /**
     * @param  list<string>  $row
     */
    private function rowIsEmpty(array $row): bool
    {
        foreach ($row as $cell) {
            if ($cell !== '') {
                return false;
            }
        }

        return true;
    }

    private function friendlyError(RequestException $exception): string
    {
        $status = $exception->response->status();
        $message = (string) $exception->response->json('error.message', '');

        if ($status === 403 || $status === 401) {
            return __('Could not access this Google Sheet. Make sure it is shared as “Anyone with the link can view”.');
        }

        if ($status === 404) {
            return __('Google Sheet not found. Check the URL and try again.');
        }

        if ($message !== '') {
            return $message;
        }

        return __('Google Sheets verification failed.');
    }
}
