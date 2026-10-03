<?php

use App\Support\GoogleSheets\GoogleSheetsApiClient;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

uses(TestCase::class);

test('rows after reads from a1 and skips the header and already synced rows', function () {
    Http::preventStrayRequests();
    Http::fake([
        'https://sheets.googleapis.com/v4/spreadsheets/*/values/*' => Http::response([
            'values' => [
                ['Name', 'Phone'],
                ['Riya', '9876543210'],
                ['Asha', '9000000001'],
            ],
        ], 200),
    ]);

    config(['services.google_sheets.api_key' => 'test-key']);

    $rows = (new GoogleSheetsApiClient)->rowsAfter('1SpreadsheetId', 'Sheet1', 1);

    expect($rows)->toBe([
        ['row' => 2, 'values' => ['Riya', '9876543210']],
        ['row' => 3, 'values' => ['Asha', '9000000001']],
    ]);

    Http::assertSent(function ($request): bool {
        return str_contains(
            urldecode($request->url()),
            "/values/'Sheet1'!A1:ZZ",
        );
    });
});

test('header row reads from a1', function () {
    Http::preventStrayRequests();
    Http::fake([
        'https://sheets.googleapis.com/v4/spreadsheets/*/values/*' => Http::response([
            'values' => [
                ['Name', 'Phone'],
                ['Riya', '9876543210'],
            ],
        ], 200),
    ]);

    config(['services.google_sheets.api_key' => 'test-key']);

    $headers = (new GoogleSheetsApiClient)->headerRow('1SpreadsheetId', 'Sheet1');

    expect($headers)->toBe(['Name', 'Phone']);

    Http::assertSent(function ($request): bool {
        return str_contains(
            urldecode($request->url()),
            "/values/'Sheet1'!A1:ZZ",
        );
    });
});
