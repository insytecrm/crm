<?php

use App\Models\Tenant;

test('tenants sync registry registers missing central records for existing tenant databases', function () {
    if (config('database.default') !== 'mysql') {
        test()->markTestSkipped('Requires MySQL tenant databases.');
    }

    Tenant::query()->whereKey('acme')->delete();

    $this->artisan('tenants:sync-registry')
        ->assertSuccessful();

    expect(Tenant::query()->whereKey('acme')->exists())->toBeTrue();
});
