<?php

use App\Models\BillingInvoice;
use App\Models\PartnerSubscription;
use App\Models\PlatformLead;
use App\Models\Quotation;
use App\Models\Tenant;
use App\Models\User;

test('platform reset command requires force flag', function () {
    $this->artisan('platform:reset-demo-data')
        ->assertFailed();
});

test('platform reset command removes demo data but keeps super admins', function () {
    $admin = User::factory()->superAdmin()->create([
        'email' => 'admin@platform.com',
    ]);
    User::factory()->create([
        'email' => 'regular@platform.com',
    ]);

    $tenant = Tenant::factory()->create(['id' => 'demo-partner']);
    PartnerSubscription::factory()->create(['tenant_id' => $tenant->id]);
    PlatformLead::factory()->create();
    Quotation::factory()->create(['tenant_id' => $tenant->id]);
    BillingInvoice::factory()->create(['tenant_id' => $tenant->id]);

    $this->artisan('platform:reset-demo-data --force')
        ->assertSuccessful()
        ->expectsOutputToContain('Platform demo data reset complete.');

    expect(Tenant::query()->count())->toBe(0)
        ->and(PlatformLead::query()->count())->toBe(0)
        ->and(Quotation::query()->count())->toBe(0)
        ->and(PartnerSubscription::query()->count())->toBe(0)
        ->and(BillingInvoice::query()->count())->toBe(0)
        ->and(User::query()->whereKey($admin->id)->exists())->toBeTrue()
        ->and(User::query()->where('email', 'regular@platform.com')->exists())->toBeFalse();
});
