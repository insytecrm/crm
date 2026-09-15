<?php

use App\Enums\UtilityMailEncryption;

test('utility mail encryption maps to laravel smtp schemes', function (UtilityMailEncryption $encryption, ?string $expectedScheme) {
    expect($encryption->scheme())->toBe($expectedScheme);
})->with([
    'tls starttls' => [UtilityMailEncryption::Tls, 'smtp'],
    'ssl implicit tls' => [UtilityMailEncryption::Ssl, 'smtps'],
    'none' => [UtilityMailEncryption::None, null],
]);
