<?php

use App\Enums\LeadStatus;
use App\Models\Booking;
use App\Models\Lead;
use App\Models\Property;

test('tenant sidebar shows leads menu with priority converted lost and duplicate submenus', function () {
    createTestTenant();
    actingAsTenantUser();

    $this->get('/acme/dashboard')
        ->assertOk()
        ->assertSee('Leads')
        ->assertSee('Priority Leads')
        ->assertSee('Converted Leads')
        ->assertSee('Lost Leads')
        ->assertSee('Duplicate Leads')
        ->assertSee(route('tenant.leads.index', ['tenant' => 'acme'], false))
        ->assertSee(route('tenant.leads.priority.index', ['tenant' => 'acme'], false))
        ->assertSee(route('tenant.leads.converted.index', ['tenant' => 'acme'], false))
        ->assertSee(route('tenant.leads.lost.index', ['tenant' => 'acme'], false))
        ->assertSee(route('tenant.leads.duplicates.index', ['tenant' => 'acme'], false));
});

test('tenant users can view priority leads list', function () {
    createTestTenant();
    actingAsTenantUser();

    Lead::factory()->priority()->create(['name' => 'Priority Lead']);
    Lead::factory()->create([
        'name' => 'Regular Lead',
        'lead_score' => 10,
        'next_follow_up_at' => null,
        'upcoming_site_visit_at' => null,
    ]);

    $this->get('/acme/leads/priority')
        ->assertOk()
        ->assertSee('Priority Leads')
        ->assertSee('Priority Lead')
        ->assertDontSee('Regular Lead');
});

test('tenant users can view converted leads list', function () {
    createTestTenant();
    actingAsTenantUser();

    Lead::factory()->converted()->create(['name' => 'Converted Lead']);
    Lead::factory()->create(['name' => 'Open Lead']);

    $this->get('/acme/leads/converted')
        ->assertOk()
        ->assertSee('Converted Leads')
        ->assertSee('Converted Lead')
        ->assertDontSee('Open Lead');
});

test('converted leads list does not show create booking action', function () {
    createTestTenant();
    actingAsTenantUser();

    $lead = Lead::factory()->converted()->create(['name' => 'Converted Without Booking']);

    $this->get('/acme/leads/converted')
        ->assertOk()
        ->assertSee('Converted Without Booking', false)
        ->assertDontSee('create-booking-'.$lead->id, false);
});

test('converted leads list shows booking date and property booked columns', function () {
    createTestTenant();
    actingAsTenantUser();

    $lead = Lead::factory()->converted()->create(['name' => 'Booked Converted Lead']);
    $property = Property::factory()->create([
        'project_name' => 'Sunset Towers',
        'developer_name' => 'Skyline Developers',
        'configurations' => [
            ['name' => '3 BHK', 'carpet_area_sqft' => 1200, 'price' => 15000000, 'unit_count' => 80],
        ],
    ]);

    Booking::factory()->create([
        'lead_id' => $lead->id,
        'property_id' => $property->id,
        'configuration_name' => '3 BHK',
        'booking_date' => '2026-09-01',
    ]);

    $this->get('/acme/leads/converted')
        ->assertOk()
        ->assertSee('Booking Date', false)
        ->assertSee('Property Booked', false)
        ->assertSee('Sep 1, 2026', false)
        ->assertSee('Sunset Towers · Skyline Developers', false)
        ->assertSee('3 BHK', false);
});

test('tenant users can view lost leads list', function () {
    createTestTenant();
    actingAsTenantUser();

    Lead::factory()->lost()->create(['name' => 'Lost Lead']);
    Lead::factory()->create(['name' => 'Open Lead']);

    $this->get('/acme/leads/lost')
        ->assertOk()
        ->assertSee('Lost Leads')
        ->assertSee('Lost Lead')
        ->assertDontSee('Open Lead');
});

test('tenant users can view unassigned leads list', function () {
    createTestTenant();
    actingAsTenantUser();

    Lead::factory()->unassigned()->create(['name' => 'Unassigned Lead']);
    Lead::factory()->create(['name' => 'Assigned Lead']);

    $this->get('/acme/leads/unassigned')
        ->assertOk()
        ->assertSee('Unassigned Leads')
        ->assertSee('Unassigned Lead')
        ->assertDontSee('Assigned Lead');
});

test('leads index stat cards filter the list on the same page', function () {
    createTestTenant();
    actingAsTenantUser();

    Lead::factory()->lost()->create(['name' => 'Lost Lead']);
    Lead::factory()->create(['name' => 'Open Lead']);

    $this->get('/acme/leads?filter=lost')
        ->assertOk()
        ->assertSee('Lost Leads')
        ->assertSee('Lost Lead')
        ->assertDontSee('Open Lead');
});

test('leads index filters new and follow-up due leads', function () {
    createTestTenant();
    actingAsTenantUser();

    Lead::factory()->create(['name' => 'New Lead', 'status' => LeadStatus::New]);
    Lead::factory()->followUpDue()->create(['name' => 'Due Lead']);
    Lead::factory()->create(['name' => 'Other Lead', 'status' => LeadStatus::Contacted]);

    $this->get('/acme/leads?filter=new')
        ->assertOk()
        ->assertSee('New Leads')
        ->assertSee('New Lead')
        ->assertDontSee('Due Lead')
        ->assertDontSee('Other Lead');

    $this->get('/acme/leads?filter=follow_up_due')
        ->assertOk()
        ->assertSee('Due Lead')
        ->assertDontSee('Other Lead');
});

test('main leads page remains unchanged and shows all leads', function () {
    createTestTenant();
    actingAsTenantUser();

    Lead::factory()->create(['name' => 'Open Lead']);
    Lead::factory()->converted()->create(['name' => 'Converted Lead']);
    Lead::factory()->lost()->create(['name' => 'Lost Lead']);

    $this->get('/acme/leads')
        ->assertOk()
        ->assertSee('Leads')
        ->assertSee('Open Lead')
        ->assertSee('Converted Lead')
        ->assertSee('Lost Lead');
});
