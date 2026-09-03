<?php

use App\Enums\LeadBudget;
use App\Enums\LeadLostReason;
use App\Enums\LeadScheduledEventStatus;
use App\Enums\LeadStatus;
use App\Enums\PropertyType;
use App\Enums\SiteVisitType;
use App\Models\Lead;

test('guests are redirected from leads page', function () {
    createTestTenant();

    $this->get('/acme/leads')
        ->assertRedirect(route('tenant.login', ['tenant' => 'acme']));
});

test('tenant users can view leads list', function () {
    createTestTenant();
    actingAsTenantUser();

    $this->get('/acme/leads')
        ->assertOk()
        ->assertSee('Leads')
        ->assertSee('Total Leads');
});

test('leads list shows database-driven statistics', function () {
    createTestTenant();
    actingAsTenantUser();

    Lead::factory()->count(3)->create(['status' => LeadStatus::New]);
    Lead::factory()->create(['status' => LeadStatus::Converted]);
    Lead::factory()->lost()->create();
    Lead::factory()->unassigned()->create();
    Lead::factory()->followUpDue()->create();

    $this->get('/acme/leads')
        ->assertOk()
        ->assertSee('Unassigned')
        ->assertSee('Lost')
        ->assertSee('6')
        ->assertSee('3')
        ->assertSee('1');
});

test('tenant users can search leads by name', function () {
    createTestTenant();
    actingAsTenantUser();

    Lead::factory()->create(['name' => 'John Smith']);
    Lead::factory()->create(['name' => 'Jane Doe']);

    $this->get('/acme/leads?search=John')
        ->assertOk()
        ->assertSee('John Smith')
        ->assertDontSee('Jane Doe');
});

test('tenant users can create a lead', function () {
    createTestTenant();
    actingAsTenantUser();

    $this->post('/acme/leads', [
        'name' => 'New Prospect',
        'phone' => '+91 9876543210',
        'email' => 'prospect@example.com',
        'source' => 'Website',
        'budget' => LeadBudget::FiftyLakhToSeventyLakh->value,
        'location' => 'Mumbai',
        'property_type' => PropertyType::Apartment->value,
        'configuration' => '2 BHK',
    ])->assertRedirect(route('tenant.leads.index', ['tenant' => 'acme']));

    expect(Lead::query()->where('name', 'New Prospect')->exists())->toBeTrue();
});

test('tenant users can create a lead with a budget range', function () {
    createTestTenant();
    actingAsTenantUser();

    $this->post('/acme/leads', [
        'name' => 'Budget Lead',
        'budget' => LeadBudget::NinetyLakhToOnePointTwoCrore->value,
    ])->assertRedirect(route('tenant.leads.index', ['tenant' => 'acme']));

    $lead = Lead::query()->where('name', 'Budget Lead')->first();

    expect($lead)->not->toBeNull()
        ->and($lead->budget)->toBe(LeadBudget::NinetyLakhToOnePointTwoCrore);
});

test('leads list renders read-only requirement card', function () {
    createTestTenant();
    actingAsTenantUser();

    $lead = Lead::factory()->create([
        'budget' => LeadBudget::BelowFiftyLakh,
        'property_type' => PropertyType::Apartment,
        'location' => 'Bhugaon',
        'configuration' => '2 BHK',
    ]);

    $this->get('/acme/leads')
        ->assertOk()
        ->assertSee('Requirement')
        ->assertSee('Bhugaon')
        ->assertSee('2 BHK')
        ->assertSee(LeadBudget::BelowFiftyLakh->label())
        ->assertSee(PropertyType::Apartment->label())
        ->assertDontSee('lead-budget-'.$lead->id, false)
        ->assertDontSee('lead-property-type-'.$lead->id, false);
});

test('edit columns modal uses shared lead table preferences store', function () {
    createTestTenant();
    actingAsTenantUser();

    $this->get('/acme/leads')
        ->assertOk()
        ->assertSee("x-on:open-modal.window=\"\$event.detail == 'edit-columns' ? openModal() : null\"", false)
        ->assertSee('x-model="$store.leadTablePreferences.columns.phone"', false)
        ->assertSee('x-model="$store.leadTablePreferences.columns.requirement"', false)
        ->assertSee('x-model="$store.leadTablePreferences.actions.call"', false)
        ->assertSee('$store.leadTablePreferences.persistPreferences()', false)
        ->assertSee('$store.leadTablePreferences.ensureLoaded()', false);
});

test('tenant users can filter leads list by column filters', function () {
    createTestTenant();
    actingAsTenantUser();

    Lead::factory()->create([
        'name' => 'Mumbai Lead',
        'status' => LeadStatus::New,
        'source' => 'Website',
        'budget' => LeadBudget::FiftyLakhToSeventyLakh,
        'location' => 'Mumbai',
    ]);
    Lead::factory()->create([
        'name' => 'Pune Lead',
        'status' => LeadStatus::Contacted,
        'source' => 'Referral',
        'budget' => LeadBudget::BelowFiftyLakh,
        'location' => 'Pune',
    ]);

    $this->get('/acme/leads?status='.LeadStatus::New->value.'&location=Mumbai')
        ->assertOk()
        ->assertSee('Mumbai Lead')
        ->assertDontSee('Pune Lead');
});

test('leads list shows toggle filters panel with filter fields', function () {
    createTestTenant();
    actingAsTenantUser();

    $this->get('/acme/leads')
        ->assertOk()
        ->assertSee('Filters')
        ->assertSee('Assigned To')
        ->assertSee('Property Type')
        ->assertSee('Created From')
        ->assertSee('Apply Filters');
});

test('leads list includes create booking action for leads without bookings', function () {
    createTestTenant();
    actingAsTenantUser();

    $lead = Lead::factory()->create(['phone' => '+91 9876543210']);

    $this->get('/acme/leads')
        ->assertOk()
        ->assertSee('create-booking-'.$lead->id, false)
        ->assertDontSee(route('tenant.bookings.create', ['tenant' => 'acme', 'lead' => $lead->id], false), false);
});

test('lead creation rejects invalid budget values', function () {
    createTestTenant();
    actingAsTenantUser();

    $this->from('/acme/leads')
        ->post('/acme/leads', [
            'name' => 'Invalid Budget Lead',
            'budget' => 'not-a-range',
        ])
        ->assertSessionHasErrors('budget');
});

test('leads list shows site visit stage under lead status', function () {
    createTestTenant();
    actingAsTenantUser();

    $lead = Lead::factory()->create([
        'name' => 'Site Visit Stage Lead',
        'status' => LeadStatus::SiteVisit,
        'upcoming_site_visit_at' => now()->addDay(),
    ]);
    scheduleSiteVisitForLead($lead, [
        'visit_type' => SiteVisitType::FreshVisit,
        'scheduled_at' => now()->addDay(),
        'status' => LeadScheduledEventStatus::Scheduled,
    ]);

    $this->get('/acme/leads')
        ->assertOk()
        ->assertSee('Site Visit Stage Lead')
        ->assertSee(LeadStatus::SiteVisit->label())
        ->assertSee('Fresh Visit scheduled');
});

test('leads list shows completed revisit stage under site visit status', function () {
    createTestTenant();
    actingAsTenantUser();

    $lead = Lead::factory()->create([
        'name' => 'Revisit Done Lead',
        'status' => LeadStatus::SiteVisit,
        'upcoming_site_visit_at' => null,
    ]);
    scheduleSiteVisitForLead($lead, [
        'visit_type' => SiteVisitType::Revisit,
        'status' => LeadScheduledEventStatus::Completed,
        'completed_at' => now()->subHour(),
        'scheduled_at' => now()->subDay(),
    ]);

    $this->get('/acme/leads')
        ->assertOk()
        ->assertSee('Revisit Done Lead')
        ->assertSee('Revisit done');
});

test('leads list shows follow-up stage under follow-up status', function () {
    createTestTenant();
    actingAsTenantUser();

    $lead = Lead::factory()->create([
        'name' => 'Follow Up Stage Lead',
        'status' => LeadStatus::FollowUp,
        'next_follow_up_at' => now()->addDay(),
    ]);
    scheduleFollowUpForLead($lead, [
        'sequence_number' => 2,
        'scheduled_at' => now()->addDay(),
    ]);

    $this->get('/acme/leads')
        ->assertOk()
        ->assertSee('Follow Up Stage Lead')
        ->assertSee('2nd Follow-up scheduled');
});

test('leads list shows lost reason under lost status', function () {
    createTestTenant();
    actingAsTenantUser();

    Lead::factory()->lost()->create([
        'name' => 'Lost Stage Lead',
        'lost_reasons' => [LeadLostReason::BudgetMismatch->value],
    ]);

    $this->get('/acme/leads/lost')
        ->assertOk()
        ->assertSee('Lost Stage Lead')
        ->assertSee(LeadLostReason::BudgetMismatch->label());
});
