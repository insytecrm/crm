<?php

use App\Enums\DashboardPeriod;
use App\Enums\LeadStatus;
use App\Models\Lead;
use Illuminate\Support\Carbon;

test('dashboard pipeline endpoint returns filtered chart json without leaving dashboard', function () {
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

    $this->getJson('/acme/dashboard/pipeline?period='.DashboardPeriod::ThisMonth->value)
        ->assertOk()
        ->assertJsonPath('period', DashboardPeriod::ThisMonth->value)
        ->assertJsonPath('pipeline.total', 1)
        ->assertJsonPath('pipeline.stages.0.status', LeadStatus::New->value)
        ->assertJsonPath('pipeline.stages.0.count', 1)
        ->assertJsonPath('pipeline.stages.1.count', 0);
});

test('dashboard pipeline endpoint supports custom date range', function () {
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

    $this->getJson('/acme/dashboard/pipeline?period='.DashboardPeriod::Custom->value.'&from=2026-02-01&to=2026-02-28')
        ->assertOk()
        ->assertJsonPath('period', DashboardPeriod::Custom->value)
        ->assertJsonPath('pipeline.total', 1)
        ->assertJsonPath('from', '2026-02-01')
        ->assertJsonPath('to', '2026-02-28');
});

test('dashboard pipeline stage href still points to leads with status filter', function () {
    createTestTenant();
    actingAsTenantUser();

    $this->travelTo(Carbon::parse('2026-03-15 10:00:00'));

    Lead::factory()->create(['status' => LeadStatus::SiteVisit, 'created_at' => now()]);

    $response = $this->getJson('/acme/dashboard/pipeline?period='.DashboardPeriod::ThisMonth->value)
        ->assertOk();

    $stage = collect($response->json('pipeline.stages'))
        ->firstWhere('status', LeadStatus::SiteVisit->value);

    expect($stage['href'])
        ->toContain('/acme/leads')
        ->toContain('status='.LeadStatus::SiteVisit->value);
});
