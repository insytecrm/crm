<?php

use App\Enums\DashboardPeriod;
use App\Enums\LeadStatus;
use App\Models\Lead;
use Illuminate\Support\Carbon;

test('dashboard shows sales pipeline card with lead counts by status', function () {
    createTestTenant();
    actingAsTenantUser();

    Lead::factory()->create(['status' => LeadStatus::New, 'created_at' => now()]);
    Lead::factory()->create(['status' => LeadStatus::New, 'created_at' => now()]);
    Lead::factory()->create(['status' => LeadStatus::Qualified, 'created_at' => now()]);
    Lead::factory()->create(['status' => LeadStatus::Converted, 'created_at' => now()]);

    $this->get('/acme/dashboard')
        ->assertOk()
        ->assertSee('Sales Pipeline')
        ->assertSee('This Month')
        ->assertViewHas('pipeline', function (array $pipeline): bool {
            expect($pipeline['total'])->toBe(4)
                ->and(collect($pipeline['stages'])->firstWhere('status', LeadStatus::New->value)['count'])->toBe(2)
                ->and(collect($pipeline['stages'])->firstWhere('status', LeadStatus::Qualified->value)['count'])->toBe(1)
                ->and(collect($pipeline['stages'])->firstWhere('status', LeadStatus::Converted->value)['count'])->toBe(1)
                ->and(collect($pipeline['stages'])->firstWhere('status', LeadStatus::Lost->value)['count'])->toBe(0);

            return true;
        });
});

test('dashboard pipeline date filter limits counts to the selected period', function () {
    createTestTenant();
    actingAsTenantUser();

    Lead::factory()->create([
        'status' => LeadStatus::New,
        'created_at' => Carbon::parse('2026-03-10 12:00:00'),
    ]);
    Lead::factory()->create([
        'status' => LeadStatus::Contacted,
        'created_at' => Carbon::parse('2025-01-15 12:00:00'),
    ]);

    $this->travelTo(Carbon::parse('2026-03-20 10:00:00'));

    $this->get('/acme/dashboard?period='.DashboardPeriod::ThisMonth->value)
        ->assertOk()
        ->assertViewHas('pipeline', function (array $pipeline): bool {
            expect($pipeline['total'])->toBe(1)
                ->and(collect($pipeline['stages'])->firstWhere('status', LeadStatus::New->value)['count'])->toBe(1)
                ->and(collect($pipeline['stages'])->firstWhere('status', LeadStatus::Contacted->value)['count'])->toBe(0);

            return true;
        });

    $this->get('/acme/dashboard?period='.DashboardPeriod::AllTime->value)
        ->assertOk()
        ->assertViewHas('pipeline', fn (array $pipeline): bool => $pipeline['total'] === 2);
});

test('dashboard pipeline custom range filters by from and to dates', function () {
    createTestTenant();
    actingAsTenantUser();

    Lead::factory()->create([
        'status' => LeadStatus::Qualified,
        'created_at' => Carbon::parse('2026-02-10 12:00:00'),
    ]);
    Lead::factory()->create([
        'status' => LeadStatus::New,
        'created_at' => Carbon::parse('2026-01-05 12:00:00'),
    ]);

    $this->get('/acme/dashboard?period='.DashboardPeriod::Custom->value.'&from=2026-02-01&to=2026-02-28')
        ->assertOk()
        ->assertSee('Custom Range')
        ->assertViewHas('pipeline', function (array $pipeline): bool {
            expect($pipeline['total'])->toBe(1)
                ->and(collect($pipeline['stages'])->firstWhere('status', LeadStatus::Qualified->value)['count'])->toBe(1);

            return true;
        });
});

test('dashboard pipeline stage links open leads filtered by status and period dates', function () {
    createTestTenant();
    actingAsTenantUser();

    $this->travelTo(Carbon::parse('2026-03-15 10:00:00'));

    Lead::factory()->create(['status' => LeadStatus::SiteVisit, 'created_at' => now()]);

    $response = $this->get('/acme/dashboard?period='.DashboardPeriod::ThisMonth->value)
        ->assertOk();

    $pipeline = $response->viewData('pipeline');
    $stage = collect($pipeline['stages'])->firstWhere('status', LeadStatus::SiteVisit->value);

    expect($stage['href'])
        ->toContain('status='.LeadStatus::SiteVisit->value)
        ->toContain('created_from=2026-03-01')
        ->toContain('created_to=2026-03-31');

    $this->get($stage['href'])
        ->assertOk()
        ->assertSee('Site Visit');
});

test('dashboard pipeline percentages and bar widths reflect lead distribution', function () {
    createTestTenant();
    actingAsTenantUser();

    Lead::factory()->count(3)->create(['status' => LeadStatus::New, 'created_at' => now()]);
    Lead::factory()->create(['status' => LeadStatus::Lost, 'created_at' => now()]);

    $this->get('/acme/dashboard?period='.DashboardPeriod::AllTime->value)
        ->assertOk()
        ->assertViewHas('pipeline', function (array $pipeline): bool {
            $new = collect($pipeline['stages'])->firstWhere('status', LeadStatus::New->value);
            $lost = collect($pipeline['stages'])->firstWhere('status', LeadStatus::Lost->value);

            expect($new['percentage'])->toBe(75.0)
                ->and($lost['percentage'])->toBe(25.0)
                ->and($new['bar_percent'])->toBe(100.0)
                ->and($lost['bar_percent'])->toBe(33.3);

            return true;
        });
});
