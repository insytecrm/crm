<?php

use App\Enums\LeadStatus;
use App\Enums\PropertyType;
use App\Models\Lead;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Carbon;

test('guests are redirected from reports export and print', function () {
    createTestTenant();

    $this->get('/acme/reports/export')
        ->assertRedirect(route('tenant.login', ['tenant' => 'acme']));

    $this->get('/acme/reports/print')
        ->assertRedirect(route('tenant.login', ['tenant' => 'acme']));
});

test('agent cannot export or print reports', function () {
    createTestTenant();
    actingAsTenantUser();

    $agentRole = Role::query()->where('slug', 'agent')->firstOrFail();
    $agent = User::query()->create([
        'name' => 'Agent User',
        'email' => 'agent-reports-export@acme.test',
        'password' => 'password',
        'role_id' => $agentRole->id,
        'email_verified_at' => now(),
    ]);

    actingAsTenantUser($agent);

    $this->get('/acme/reports/export')->assertForbidden();
    $this->get('/acme/reports/print')->assertForbidden();
});

test('reports export downloads excel spreadsheet for the selected period', function () {
    createTestTenant();
    $admin = actingAsTenantUser();
    $this->travelTo(Carbon::parse('2026-03-15 10:00:00'));

    Lead::factory()->create([
        'status' => LeadStatus::New,
        'property_type' => PropertyType::Apartment,
        'assigned_to_id' => $admin->id,
        'name' => 'March Lead',
        'created_at' => Carbon::parse('2026-03-10 12:00:00'),
    ]);
    Lead::factory()->create([
        'status' => LeadStatus::Qualified,
        'property_type' => PropertyType::Villa,
        'assigned_to_id' => $admin->id,
        'name' => 'February Lead',
        'created_at' => Carbon::parse('2026-02-10 12:00:00'),
    ]);

    $response = $this->get('/acme/reports/export?period=this_month');

    $response
        ->assertOk()
        ->assertHeader('content-disposition');

    expect($response->headers->get('content-type'))->toContain('application/vnd.ms-excel');

    $content = $response->streamedContent();

    expect($content)
        ->toContain('Worksheet')
        ->toContain('Summary')
        ->toContain('New Leads')
        ->toContain('Leads by Status')
        ->toContain('Agent Performance')
        ->toContain('This Month')
        ->toContain('Apartment')
        ->toContain('Conversion Ratio')
        ->not->toContain('Villa');
});

test('reports print page renders filtered analytics for a4 printing', function () {
    createTestTenant();
    $admin = actingAsTenantUser();
    $this->travelTo(Carbon::parse('2026-03-15 10:00:00'));

    Lead::factory()->create([
        'status' => LeadStatus::New,
        'property_type' => PropertyType::Apartment,
        'assigned_to_id' => $admin->id,
        'created_at' => Carbon::parse('2026-03-10 12:00:00'),
    ]);
    Lead::factory()->create([
        'status' => LeadStatus::Qualified,
        'property_type' => PropertyType::Villa,
        'assigned_to_id' => $admin->id,
        'created_at' => Carbon::parse('2026-02-10 12:00:00'),
    ]);

    $this->get('/acme/reports/print?period=this_month')
        ->assertOk()
        ->assertSee('Reports')
        ->assertSee('This Month')
        ->assertSee('size: A4', false)
        ->assertSee('Apartment')
        ->assertDontSee('Villa')
        ->assertSee('Agent Performance')
        ->assertSee('Conversion Ratio')
        ->assertSee('data-report-donut', false)
        ->assertSee('newLeadsBarFill', false)
        ->assertViewHas('analytics', fn (array $analytics): bool => $analytics['kpis']['total_leads'] === 1);
});
