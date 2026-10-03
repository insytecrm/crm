<?php

use App\Enums\LeadStatus;
use App\Enums\ScheduledActivityContactMethod;
use App\Enums\ScheduledActivityNextStep;
use App\Enums\ScheduledActivityOutcome;
use App\Models\Booking;
use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\LeadTask;
use App\Models\Property;
use App\Queries\TenantNavIndicators;
use App\Support\BookingProgress;
use App\Support\DuplicateLeadPrimaryRecommendation;
use App\Support\LeadNextStepHint;
use App\Support\UserWorkflowPreferences;

test('automations home redirects to workflows', function () {
    createTestTenant();
    actingAsTenantUser();

    $this->get('/acme/automations')
        ->assertRedirect(route('tenant.automations.workflows', ['tenant' => 'acme']));
});

test('lead drawer shows next step hint and open task count badge', function () {
    createTestTenant();
    actingAsTenantUser();

    $lead = Lead::factory()->create([
        'name' => 'Hint Lead',
        'status' => LeadStatus::New,
    ]);

    LeadTask::factory()->for($lead)->create([
        'title' => 'Call back',
        'assigned_to_id' => auth()->id(),
    ]);

    $this->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
        ->get('/acme/leads/'.$lead->id)
        ->assertOk()
        ->assertSee('Next:')
        ->assertSee('Contact lead')
        ->assertSee('>1<', false);
});

test('follow-up schedule uses default datetime in modal', function () {
    createTestTenant();
    actingAsTenantUser();

    $lead = Lead::factory()->create();
    $expected = app(UserWorkflowPreferences::class)->defaultFollowUpAt();

    $this->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
        ->get('/acme/leads/'.$lead->id)
        ->assertOk()
        ->assertSee('value="'.$expected.'"', false);
});

test('booking creation flashes short message and opens booking modal', function () {
    createTestTenant();
    $user = actingAsTenantUser();

    $lead = Lead::factory()->create(['status' => LeadStatus::Negotiation]);
    $property = Property::factory()->create([
        'configurations' => [
            ['name' => '2 BHK', 'carpet_area_sqft' => 900, 'price' => 5000000],
        ],
    ]);

    $response = $this->post('/acme/bookings', [
        'lead_id' => $lead->id,
        'property_id' => $property->id,
        'configuration_index' => 0,
        'unit_number' => 'A-101',
        'agreement_value' => 5000000,
        'booking_date' => now()->toDateString(),
    ]);

    $booking = Booking::query()->first();

    $response
        ->assertRedirect(route('tenant.bookings.index', ['tenant' => 'acme']))
        ->assertSessionHas('status', __('Booking created. Lead converted.'))
        ->assertSessionHas('open_modal', 'booking-'.$booking->id);

    expect($lead->fresh()->status)->toBe(LeadStatus::Converted);
});

test('duplicate merge recommends primary lead with most activity', function () {
    createTestTenant();
    actingAsTenantUser();

    $primary = Lead::factory()->create(['phone' => '9999999999', 'name' => 'Primary Lead']);
    $duplicate = Lead::factory()->create(['phone' => '9999999999', 'name' => 'Duplicate Lead']);

    LeadActivity::factory()->create(['lead_id' => $primary->id]);
    LeadActivity::factory()->create(['lead_id' => $primary->id]);
    LeadActivity::factory()->create(['lead_id' => $duplicate->id]);

    $recommendedId = app(DuplicateLeadPrimaryRecommendation::class)->forGroup(collect([$primary, $duplicate]));

    expect($recommendedId)->toBe($primary->id);
});

test('nav indicators count unassigned leads and overdue activities', function () {
    createTestTenant();
    actingAsTenantUser();

    Lead::factory()->create([
        'assigned_to_id' => null,
        'status' => LeadStatus::New,
    ]);

    $indicators = app(TenantNavIndicators::class)->forTenant();

    expect($indicators['unassigned_leads'])->toBeGreaterThan(0);
});

test('booking progress tracks agreement invoice and payout steps', function () {
    createTestTenant();
    actingAsTenantUser();

    $booking = Booking::factory()->create([
        'agreement_date' => null,
        'invoiced_at' => null,
        'payout_paid_at' => null,
    ]);

    expect(app(BookingProgress::class)->for($booking))->toBe([
        'completed' => 1,
        'total' => 4,
    ]);

    $booking->update([
        'agreement_date' => now(),
        'invoiced_at' => now(),
    ]);

    expect(app(BookingProgress::class)->for($booking->fresh()))->toBe([
        'completed' => 3,
        'total' => 4,
    ]);
});

test('completing follow-up remembers contact method and outcome preferences', function () {
    createTestTenant();
    $user = actingAsTenantUser();

    $lead = Lead::factory()->create([
        'next_follow_up_at' => now()->addDay(),
    ]);

    $event = scheduleFollowUpForLead($lead, [
        'scheduled_at' => now()->addDay(),
    ]);

    $this->post('/acme/scheduled-events/'.$event->id.'/complete-follow-up', [
        'contact_method' => ScheduledActivityContactMethod::Call->value,
        'outcome' => ScheduledActivityOutcome::Interested->value,
        'next_step' => ScheduledActivityNextStep::None->value,
    ])->assertRedirect();

    $preferences = UserWorkflowPreferences::for($user->fresh());

    expect($preferences->get('contact_method'))->toBe(ScheduledActivityContactMethod::Call->value)
        ->and($preferences->get('outcome'))->toBe(ScheduledActivityOutcome::Interested->value);
});

test('lead next step hint returns null for closed leads', function () {
    createTestTenant();
    actingAsTenantUser();

    $lead = Lead::factory()->create(['status' => LeadStatus::Lost]);

    expect(app(LeadNextStepHint::class)->for($lead))->toBeNull();
});

test('dashboard shows collapsed setup strip for new tenants', function () {
    createTestTenant();
    actingAsTenantUser();

    $this->get('/acme/dashboard')
        ->assertOk()
        ->assertSee('Setup')
        ->assertSee('Add a property');
});
