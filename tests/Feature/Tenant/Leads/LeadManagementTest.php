<?php

use App\Enums\LeadActivityType;
use App\Enums\LeadBudget;
use App\Enums\LeadClosingReason;
use App\Enums\LeadListingFilter;
use App\Enums\LeadLostReason;
use App\Enums\LeadScheduledEventStatus;
use App\Enums\LeadScheduledEventType;
use App\Enums\LeadSource;
use App\Enums\LeadStatus;
use App\Enums\PropertyType;
use App\Enums\ScheduledActivityPriority;
use App\Enums\SiteVisitNextStep;
use App\Enums\SiteVisitOutcome;
use App\Enums\SiteVisitType;
use App\Models\Booking;
use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\Property;
use App\Support\LeadDrawerRedirect;
use App\Support\LeadTablePreferences;

test('tenant users can load lead details drawer via ajax', function () {
    createTestTenant();
    actingAsTenantUser();

    $lead = Lead::factory()->create(['name' => 'Detail Lead']);

    $this->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
        ->get('/acme/leads/'.$lead->id)
        ->assertOk()
        ->assertSee('Detail Lead')
        ->assertSee('Activity Timeline')
        ->assertSee('Edit Lead')
        ->assertSee('Save Changes')
        ->assertSee('History')
        ->assertSee('AI Insight')
        ->assertSee('$dispatch(\'open-modal\', \'create-booking\')', false)
        ->assertSee('lead-details-title', false);
});

test('lead drawer details tab shows property interest from completed site visits', function () {
    createTestTenant();
    actingAsTenantUser();

    $firstProperty = Property::factory()->create([
        'project_name' => 'Alpha Towers',
        'developer_name' => 'Alpha Developers',
    ]);
    $secondProperty = Property::factory()->create([
        'project_name' => 'Beta Homes',
        'developer_name' => 'Beta Developers',
    ]);

    $lead = Lead::factory()->create(['name' => 'Property Interest Lead']);

    scheduleSiteVisitForLead($lead, [
        'sequence_number' => 1,
        'status' => LeadScheduledEventStatus::Completed,
        'property_id' => $firstProperty->id,
        'completed_at' => now()->subDay(),
    ]);

    scheduleSiteVisitForLead($lead, [
        'sequence_number' => 2,
        'status' => LeadScheduledEventStatus::Completed,
        'property_id' => $secondProperty->id,
        'completed_at' => now(),
    ]);

    $this->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
        ->get('/acme/leads/'.$lead->id)
        ->assertOk()
        ->assertSee('Property Interest', false)
        ->assertSee('Alpha Towers · Alpha Developers', false)
        ->assertSee('Beta Homes · Beta Developers', false);
});

test('leads list can show property interest column', function () {
    createTestTenant();
    $user = actingAsTenantUser();

    $property = Property::factory()->create([
        'project_name' => 'List Interest Towers',
        'developer_name' => 'List Developers',
    ]);

    $lead = Lead::factory()->create(['name' => 'List Property Interest Lead']);

    scheduleSiteVisitForLead($lead, [
        'status' => LeadScheduledEventStatus::Completed,
        'property_id' => $property->id,
        'completed_at' => now(),
    ]);

    $preferences = $user->preferences ?? [];
    $preferences[LeadTablePreferences::StorageKey] = [
        LeadListingFilter::All->value => LeadTablePreferences::normalize([
            'columns' => array_merge(LeadTablePreferences::defaults()['columns'], [
                'property_interest' => true,
            ]),
            'actions' => LeadTablePreferences::defaults()['actions'],
        ]),
    ];
    $user->forceFill(['preferences' => $preferences])->save();

    $this->get('/acme/leads')
        ->assertOk()
        ->assertSee('Property Interest', false)
        ->assertSee('List Interest Towers · List Developers', false)
        ->assertSee('List Property Interest Lead', false);
});

test('lead drawer hides create booking when lead already has a booking', function () {
    createTestTenant();
    actingAsTenantUser();

    $lead = Lead::factory()->create(['name' => 'Booked Lead']);
    Booking::factory()->create(['lead_id' => $lead->id]);

    $this->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
        ->get('/acme/leads/'.$lead->id)
        ->assertOk()
        ->assertSee('Booked Lead')
        ->assertDontSee('$dispatch(\'open-modal\', \'create-booking\')', false)
        ->assertDontSee('name="create-booking"', false);
});

test('leads index includes lead drawer host for in-place opening', function () {
    createTestTenant();
    actingAsTenantUser();

    $lead = Lead::factory()->create(['name' => 'Detail Lead']);

    $this->get('/acme/leads?lead='.$lead->id)
        ->assertOk()
        ->assertSee('id="lead-drawer-host-root"', false)
        ->assertSee('data-lead-hover-card', false)
        ->assertSee('"id":'.$lead->id, false)
        ->assertSee('Detail Lead', false);
});

test('tenant users can update a lead from the drawer', function () {
    createTestTenant();
    actingAsTenantUser();

    $lead = Lead::factory()->create([
        'name' => 'Before Edit',
        'phone' => '+91 9000000000',
    ]);

    $this->from('/acme/leads?lead='.$lead->id)
        ->patch('/acme/leads/'.$lead->id, [
            'name' => 'After Edit',
            'phone' => '+91 9111111111',
            'email' => $lead->email,
            'source' => $lead->source,
            'budget' => $lead->budget?->value,
            'location' => $lead->location,
            'property_type' => $lead->property_type?->value,
            'configuration' => $lead->configuration,
            'assigned_to_id' => $lead->assigned_to_id,
            'lead_score' => $lead->lead_score,
            'next_action' => $lead->next_action,
        ])
        ->assertRedirect(route('tenant.leads.index', ['tenant' => 'acme', 'lead' => $lead->id]))
        ->assertSessionHas('status');

    $lead->refresh();

    expect($lead->name)->toBe('After Edit')
        ->and($lead->phone)->toBe('+91 9111111111');
});

test('leads list shows lead status as read-only', function () {
    createTestTenant();
    actingAsTenantUser();

    $lead = Lead::factory()->create([
        'name' => 'Status Badge Lead',
        'status' => LeadStatus::Contacted,
    ]);

    $this->get('/acme/leads')
        ->assertOk()
        ->assertSee('Status Badge Lead')
        ->assertSee(LeadStatus::Contacted->label())
        ->assertDontSee('lead-status-'.$lead->id, false)
        ->assertDontSee(route('tenant.leads.status.update', $lead, false), false);
});

test('lead drawer still allows editing lead status', function () {
    createTestTenant();
    actingAsTenantUser();

    $lead = Lead::factory()->create([
        'name' => 'Drawer Status Lead',
        'status' => LeadStatus::New,
    ]);

    $this->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
        ->get('/acme/leads/'.$lead->id)
        ->assertOk()
        ->assertSee('lead-status-'.$lead->id, false)
        ->assertSee(route('tenant.leads.status.update', $lead, false), false);
});

test('tenant users can update lead status with listing redirect', function () {
    createTestTenant();
    actingAsTenantUser();

    $lead = Lead::factory()->create(['status' => LeadStatus::New]);

    $this->from('/acme/leads/priority')
        ->patch('/acme/leads/'.$lead->id.'/status', [
            'status' => LeadStatus::Contacted->value,
            'redirect_to_listing' => '1',
        ])->assertRedirect('/acme/leads/priority');

    $lead->refresh();

    expect($lead->status)->toBe(LeadStatus::Contacted);
});

test('lead status cannot be set to converted manually', function () {
    createTestTenant();
    actingAsTenantUser();

    $lead = Lead::factory()->create(['status' => LeadStatus::Negotiation]);

    $this->withHeaders([
        'X-Requested-With' => 'XMLHttpRequest',
        'Accept' => 'application/json',
    ])
        ->patchJson('/acme/leads/'.$lead->id.'/status', [
            'status' => LeadStatus::Converted->value,
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['status']);

    expect($lead->fresh()->status)->toBe(LeadStatus::Negotiation);
});

test('lead status can be updated via ajax without a redirect', function () {
    createTestTenant();
    actingAsTenantUser();

    $lead = Lead::factory()->create(['status' => LeadStatus::New]);

    $this->withHeaders([
        'X-Requested-With' => 'XMLHttpRequest',
        'Accept' => 'application/json',
    ])
        ->patch('/acme/leads/'.$lead->id.'/status', [
            'status' => LeadStatus::Qualified->value,
        ])
        ->assertOk()
        ->assertJson([
            'status' => LeadStatus::Qualified->value,
            'label' => LeadStatus::Qualified->label(),
        ]);

    $lead->refresh();

    expect($lead->status)->toBe(LeadStatus::Qualified);
});

test('lead status can be updated via ajax with redirect to listing flag', function () {
    createTestTenant();
    actingAsTenantUser();

    $lead = Lead::factory()->create(['status' => LeadStatus::New]);

    $this->withHeaders([
        'X-Requested-With' => 'XMLHttpRequest',
        'Accept' => 'application/json',
    ])->post('/acme/leads/'.$lead->id.'/status', [
        '_method' => 'PATCH',
        'status' => LeadStatus::Contacted->value,
        'redirect_to_listing' => '1',
    ])
        ->assertOk()
        ->assertJson([
            'status' => LeadStatus::Contacted->value,
            'label' => LeadStatus::Contacted->label(),
        ]);

    $lead->refresh();

    expect($lead->status)->toBe(LeadStatus::Contacted);
});

test('lead status can be updated via post method spoof like browser fetch', function () {
    createTestTenant();
    actingAsTenantUser();

    $lead = Lead::factory()->create(['status' => LeadStatus::New]);

    $this->withHeaders([
        'X-Requested-With' => 'XMLHttpRequest',
        'Accept' => 'application/json',
    ])->post('/acme/leads/'.$lead->id.'/status', [
        '_method' => 'PATCH',
        'status' => LeadStatus::Contacted->value,
    ])
        ->assertOk()
        ->assertJson([
            'status' => LeadStatus::Contacted->value,
        ]);

    $lead->refresh();

    expect($lead->status)->toBe(LeadStatus::Contacted);
});

test('lead budget can be updated via ajax without a redirect', function () {
    createTestTenant();
    actingAsTenantUser();

    $lead = Lead::factory()->create(['budget' => LeadBudget::BelowFiftyLakh]);

    $this->withHeaders([
        'X-Requested-With' => 'XMLHttpRequest',
        'Accept' => 'application/json',
    ])
        ->patch('/acme/leads/'.$lead->id.'/budget', [
            'budget' => LeadBudget::FiftyLakhToSeventyLakh->value,
        ])
        ->assertOk()
        ->assertJson([
            'budget' => LeadBudget::FiftyLakhToSeventyLakh->value,
            'label' => LeadBudget::FiftyLakhToSeventyLakh->label(),
        ]);

    $lead->refresh();

    expect($lead->budget)->toBe(LeadBudget::FiftyLakhToSeventyLakh);
});

test('lead property type can be updated via ajax without a redirect', function () {
    createTestTenant();
    actingAsTenantUser();

    $lead = Lead::factory()->create(['property_type' => PropertyType::Apartment]);

    $this->withHeaders([
        'X-Requested-With' => 'XMLHttpRequest',
        'Accept' => 'application/json',
    ])
        ->patch('/acme/leads/'.$lead->id.'/property-type', [
            'property_type' => PropertyType::Villa->value,
        ])
        ->assertOk()
        ->assertJson([
            'property_type' => PropertyType::Villa->value,
            'label' => PropertyType::Villa->label(),
        ]);

    $lead->refresh();

    expect($lead->property_type)->toBe(PropertyType::Villa);
});

test('tenant users can update lead status and log activity', function () {
    createTestTenant();
    actingAsTenantUser();

    $lead = Lead::factory()->create(['status' => LeadStatus::New]);

    $this->from('/acme/leads?lead='.$lead->id)
        ->patch('/acme/leads/'.$lead->id.'/status', [
            'status' => LeadStatus::Contacted->value,
        ])->assertRedirect('/acme/leads?lead='.$lead->id);

    $lead->refresh();

    expect($lead->status)->toBe(LeadStatus::Contacted)
        ->and(LeadActivity::query()->where('lead_id', $lead->id)->where('type', LeadActivityType::StatusChanged)->exists())->toBeTrue();
});

test('tenant users can mark a lead lost with reasons and note', function () {
    createTestTenant();
    actingAsTenantUser();

    $lead = Lead::factory()->create(['status' => LeadStatus::Negotiation]);

    $this->from('/acme/leads?lead='.$lead->id)
        ->post('/acme/leads/'.$lead->id.'/mark-lost', [
            'lost_reasons' => [
                LeadLostReason::NotInterested->value,
                LeadLostReason::BudgetMismatch->value,
            ],
            'closing_notes' => 'Lead stopped responding after budget discussion.',
        ])->assertRedirect('/acme/leads?lead='.$lead->id);

    $lead->refresh();

    expect($lead->status)->toBe(LeadStatus::Lost)
        ->and($lead->closing_reason)->toBe(LeadClosingReason::Lost)
        ->and($lead->lost_reasons)->toBe([
            LeadLostReason::NotInterested->value,
            LeadLostReason::BudgetMismatch->value,
        ])
        ->and($lead->closing_notes)->toBe('Lead stopped responding after budget discussion.')
        ->and($lead->closed_at)->not->toBeNull();
});

test('marking a lead lost requires reasons and note', function () {
    createTestTenant();
    actingAsTenantUser();

    $lead = Lead::factory()->create(['status' => LeadStatus::Negotiation]);

    $this->from('/acme/leads?lead='.$lead->id)
        ->post('/acme/leads/'.$lead->id.'/mark-lost', [])
        ->assertSessionHasErrors(['lost_reasons', 'closing_notes']);
});

test('lead status cannot be set to lost directly', function () {
    createTestTenant();
    actingAsTenantUser();

    $lead = Lead::factory()->create(['status' => LeadStatus::Negotiation]);

    $this->from('/acme/leads?lead='.$lead->id)
        ->patch('/acme/leads/'.$lead->id.'/status', [
            'status' => LeadStatus::Lost->value,
        ])->assertSessionHasErrors('status');
});

test('creating a lead schedules an initial contact follow-up in five minutes', function () {
    createTestTenant();
    actingAsTenantUser();

    $this->travelTo('2026-09-03 12:00:00');

    $this->post('/acme/leads', [
        'name' => 'Initial Contact Lead',
        'phone' => '+91 9876543210',
        'email' => 'prospect@example.com',
        'source' => LeadSource::Referral->value,
        'budget' => LeadBudget::FiftyLakhToSeventyLakh->value,
        'location' => 'Mumbai',
        'property_type' => PropertyType::Apartment->value,
        'configuration' => '2 BHK',
    ])->assertRedirect(route('tenant.leads.index', ['tenant' => 'acme']));

    $lead = Lead::query()->where('name', 'Initial Contact Lead')->first();

    expect($lead)->not->toBeNull()
        ->and($lead->next_follow_up_at?->format('Y-m-d H:i:s'))->toBe('2026-09-03 12:05:00')
        ->and($lead->next_action)->toBe('Follow-up');

    $event = $lead->scheduledEvents()->where('type', LeadScheduledEventType::FollowUp)->first();

    expect($event)->not->toBeNull()
        ->and($event->sequence_number)->toBe(1)
        ->and($event->ordinalLabel())->toBe('Initial Contact')
        ->and($event->scheduled_at->format('Y-m-d H:i:s'))->toBe('2026-09-03 12:05:00')
        ->and($event->status)->toBe(LeadScheduledEventStatus::Scheduled);

    expect(LeadActivity::query()
        ->where('lead_id', $lead->id)
        ->where('type', LeadActivityType::FollowUpScheduled)
        ->exists())->toBeTrue();

    $this->get('/acme/follow-ups')
        ->assertOk()
        ->assertSee('Initial Contact Lead')
        ->assertSee('Initial Contact');

    $this->travelBack();
});

test('the second follow-up after initial contact is numbered', function () {
    createTestTenant();
    actingAsTenantUser();

    $this->post('/acme/leads', [
        'name' => 'Second Follow Up Lead',
        'phone' => '+91 9876543211',
    ])->assertRedirect(route('tenant.leads.index', ['tenant' => 'acme']));

    $lead = Lead::query()->where('name', 'Second Follow Up Lead')->first();

    expect($lead)->not->toBeNull();

    $this->from('/acme/leads?lead='.$lead->id)
        ->post('/acme/leads/'.$lead->id.'/follow-up/complete')
        ->assertRedirect('/acme/leads?lead='.$lead->id);

    expect($lead->fresh()->status)->toBe(LeadStatus::Contacted);

    $this->from('/acme/leads?lead='.$lead->id)
        ->post('/acme/leads/'.$lead->id.'/follow-up', [
            'next_follow_up_at' => now()->addDay()->format('Y-m-d H:i:s'),
            'priority' => ScheduledActivityPriority::Normal->value,
            'notes' => 'Second round',
        ])->assertRedirect('/acme/leads?lead='.$lead->id);

    $records = $lead->fresh()->scheduledEvents()->orderBy('sequence_number')->get();

    expect($records)->toHaveCount(2)
        ->and($records[0]->ordinalLabel())->toBe('Initial Contact')
        ->and($records[0]->status)->toBe(LeadScheduledEventStatus::Completed)
        ->and($records[1]->sequence_number)->toBe(2)
        ->and($records[1]->ordinalLabel())->toBe('2nd Follow-up')
        ->and($records[1]->status)->toBe(LeadScheduledEventStatus::Scheduled)
        ->and($records[1]->notes)->toBe('Second round');

    $this->get('/acme/follow-ups')
        ->assertOk()
        ->assertSee('Second Follow Up Lead')
        ->assertSee('2nd Follow-up');
});

test('tenant users can schedule a follow-up', function () {
    createTestTenant();
    actingAsTenantUser();

    $lead = Lead::factory()->create();
    $followUpAt = now()->addDay()->format('Y-m-d H:i:s');

    $this->from('/acme/leads?lead='.$lead->id)
        ->post('/acme/leads/'.$lead->id.'/follow-up', [
            'next_follow_up_at' => $followUpAt,
            'priority' => ScheduledActivityPriority::Normal->value,
            'notes' => 'Call back about pricing',
        ])->assertRedirect('/acme/leads?lead='.$lead->id);

    $lead->refresh();

    expect($lead->next_follow_up_at)->not->toBeNull()
        ->and($lead->scheduledEvents()->where('type', LeadScheduledEventType::FollowUp)->count())->toBe(1)
        ->and(LeadActivity::query()->where('lead_id', $lead->id)->where('type', LeadActivityType::FollowUpScheduled)->exists())->toBeTrue();
});

test('scheduling a follow-up from activities does not open the lead drawer', function () {
    createTestTenant();
    actingAsTenantUser();

    $lead = Lead::factory()->create();

    $this->from('/acme/activities?filter=today')
        ->post('/acme/leads/'.$lead->id.'/follow-up', [
            'next_follow_up_at' => now()->addDay()->format('Y-m-d H:i:s'),
            'priority' => ScheduledActivityPriority::Normal->value,
        ])
        ->assertRedirect('/acme/activities?filter=today')
        ->assertSessionHas('status');
});

test('completing a site visit ready to book stays on the current page', function () {
    createTestTenant();
    actingAsTenantUser();

    $lead = Lead::factory()->create([
        'upcoming_site_visit_at' => now()->subHour(),
    ]);
    $event = scheduleSiteVisitForLead($lead, [
        'scheduled_at' => now()->subHour(),
    ]);

    $this->from('/acme/dashboard')
        ->post('/acme/scheduled-events/'.$event->id.'/complete-site-visit', [
            'attended' => true,
            'outcome' => SiteVisitOutcome::ReadyToBook->value,
            'next_step' => SiteVisitNextStep::CreateBooking->value,
        ])
        ->assertRedirect('/acme/dashboard')
        ->assertSessionHas('status');
});

test('scheduling follow-ups creates numbered system records', function () {
    createTestTenant();
    actingAsTenantUser();

    $lead = Lead::factory()->create();

    $this->from('/acme/leads?lead='.$lead->id)
        ->post('/acme/leads/'.$lead->id.'/follow-up', [
            'next_follow_up_at' => now()->addDay()->format('Y-m-d H:i:s'),
            'priority' => ScheduledActivityPriority::Normal->value,
        ])->assertRedirect('/acme/leads?lead='.$lead->id);

    $this->from('/acme/leads?lead='.$lead->id)
        ->post('/acme/leads/'.$lead->id.'/follow-up/complete')
        ->assertRedirect('/acme/leads?lead='.$lead->id);

    $this->from('/acme/leads?lead='.$lead->id)
        ->post('/acme/leads/'.$lead->id.'/follow-up', [
            'next_follow_up_at' => now()->addDays(2)->format('Y-m-d H:i:s'),
            'priority' => ScheduledActivityPriority::Normal->value,
            'notes' => 'Second round',
        ])->assertRedirect('/acme/leads?lead='.$lead->id);

    $records = $lead->fresh()->scheduledEvents()->orderBy('sequence_number')->get();

    expect($records)->toHaveCount(2)
        ->and($records[0]->sequence_number)->toBe(1)
        ->and($records[0]->ordinalLabel())->toBe('Initial Contact')
        ->and($records[0]->status->value)->toBe('completed')
        ->and($records[1]->sequence_number)->toBe(2)
        ->and($records[1]->ordinalLabel())->toBe('2nd Follow-up')
        ->and($records[1]->status->value)->toBe('scheduled')
        ->and($records[1]->notes)->toBe('Second round');
});

test('scheduling site visits creates numbered system records', function () {
    createTestTenant();
    actingAsTenantUser();

    $lead = Lead::factory()->create();
    $property = Property::factory()->create();

    $this->from('/acme/leads?lead='.$lead->id)
        ->post('/acme/leads/'.$lead->id.'/site-visit', [
            'property_id' => $property->id,
            'visit_type' => SiteVisitType::FreshVisit->value,
            'upcoming_site_visit_at' => now()->addDay()->format('Y-m-d H:i:s'),
        ])->assertRedirect('/acme/leads?lead='.$lead->id);

    $record = $lead->fresh()->scheduledEvents()->first();

    expect($record)->not->toBeNull()
        ->and($record->sequence_number)->toBe(1)
        ->and($record->visit_type)->toBe(SiteVisitType::FreshVisit)
        ->and($record->ordinalLabel())->toBe('1st Site Visit · Fresh Visit')
        ->and($record->property_id)->toBe($property->id);
});

test('scheduling a site visit requires visit type', function () {
    createTestTenant();
    actingAsTenantUser();

    $lead = Lead::factory()->create();
    $property = Property::factory()->create();

    $this->from('/acme/leads?lead='.$lead->id)
        ->post('/acme/leads/'.$lead->id.'/site-visit', [
            'property_id' => $property->id,
            'upcoming_site_visit_at' => now()->addDay()->format('Y-m-d H:i:s'),
        ])
        ->assertSessionHasErrors('visit_type');
});

test('scheduling a revisit records revisit visit type', function () {
    createTestTenant();
    actingAsTenantUser();

    $lead = Lead::factory()->create();
    $property = Property::factory()->create();

    $this->from('/acme/leads?lead='.$lead->id)
        ->post('/acme/leads/'.$lead->id.'/site-visit', [
            'property_id' => $property->id,
            'visit_type' => SiteVisitType::Revisit->value,
            'upcoming_site_visit_at' => now()->addDay()->format('Y-m-d H:i:s'),
        ])->assertRedirect('/acme/leads?lead='.$lead->id);

    $record = $lead->fresh()->scheduledEvents()->first();

    expect($record)->not->toBeNull()
        ->and($record->visit_type)->toBe(SiteVisitType::Revisit)
        ->and($record->ordinalLabel())->toBe('1st Site Visit · Revisit');
});

test('tenant users can add notes to a lead', function () {
    createTestTenant();
    $user = actingAsTenantUser();

    $lead = Lead::factory()->create();

    $this->from('/acme/leads?lead='.$lead->id)
        ->post('/acme/leads/'.$lead->id.'/notes', [
            'body' => 'Interested in sea-facing units.',
        ])->assertRedirect('/acme/leads?lead='.$lead->id);

    expect($lead->notes()->where('body', 'Interested in sea-facing units.')->exists())->toBeTrue();
});

test('lead drawer activity timeline shows compact labels with hover detail content', function () {
    createTestTenant();
    actingAsTenantUser();

    $lead = Lead::factory()->create();

    $this->from('/acme/leads?lead='.$lead->id)
        ->post('/acme/leads/'.$lead->id.'/notes', [
            'body' => 'Interested in sea-facing units.',
        ])->assertRedirect('/acme/leads?lead='.$lead->id);

    $this->from('/acme/leads?lead='.$lead->id)
        ->post('/acme/leads/'.$lead->id.'/follow-up', [
            'next_follow_up_at' => now()->addDay()->format('Y-m-d H:i:s'),
            'priority' => ScheduledActivityPriority::Normal->value,
            'notes' => 'Discuss pricing options',
        ])->assertRedirect('/acme/leads?lead='.$lead->id);

    $this->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
        ->get('/acme/leads/'.$lead->id)
        ->assertOk()
        ->assertSee('Note Added')
        ->assertSee('Follow-up Scheduled')
        ->assertSee('data-timeline-hover', false)
        ->assertSee('Interested in sea-facing units.', false)
        ->assertSee('Discuss pricing options', false);
});

test('tenant users can bulk delete selected leads', function () {
    createTestTenant();
    actingAsTenantUser();

    $leadOne = Lead::factory()->create(['name' => 'Lead Alpha']);
    $leadTwo = Lead::factory()->create(['name' => 'Lead Beta']);
    $leadThree = Lead::factory()->create(['name' => 'Lead Gamma']);

    $this->from('/acme/leads')
        ->delete('/acme/leads/bulk', [
            'lead_ids' => [$leadOne->id, $leadTwo->id],
        ])
        ->assertRedirect(route('tenant.leads.index', ['tenant' => 'acme']))
        ->assertSessionHas('status');

    expect(Lead::query()->find($leadOne->id))->toBeNull()
        ->and(Lead::withTrashed()->find($leadOne->id))->not->toBeNull()
        ->and(Lead::query()->find($leadTwo->id))->toBeNull()
        ->and(Lead::query()->find($leadThree->id))->not->toBeNull();
});

test('bulk delete requires at least one lead id', function () {
    createTestTenant();
    actingAsTenantUser();

    $this->from('/acme/leads')
        ->delete('/acme/leads/bulk', [
            'lead_ids' => [],
        ])
        ->assertSessionHasErrors('lead_ids');
});

test('tenant users can bulk assign selected leads', function () {
    createTestTenant();
    $assignee = actingAsTenantUser();

    $leadOne = Lead::factory()->create(['name' => 'Assign Alpha', 'assigned_to_id' => null]);
    $leadTwo = Lead::factory()->create(['name' => 'Assign Beta', 'assigned_to_id' => null]);

    $this->from('/acme/leads')
        ->patch('/acme/leads/bulk/assign', [
            'lead_ids' => [$leadOne->id, $leadTwo->id],
            'assigned_to_id' => $assignee->id,
        ])
        ->assertRedirect(route('tenant.leads.index', ['tenant' => 'acme']))
        ->assertSessionHas('status');

    expect($leadOne->fresh()->assigned_to_id)->toBe($assignee->id)
        ->and($leadTwo->fresh()->assigned_to_id)->toBe($assignee->id);
});

test('tenant users can bulk update status for selected leads', function () {
    createTestTenant();
    actingAsTenantUser();

    $leadOne = Lead::factory()->create(['name' => 'Status Alpha', 'status' => LeadStatus::New]);
    $leadTwo = Lead::factory()->create(['name' => 'Status Beta', 'status' => LeadStatus::New]);

    $this->from('/acme/leads')
        ->patch('/acme/leads/bulk/status', [
            'lead_ids' => [$leadOne->id, $leadTwo->id],
            'status' => LeadStatus::Qualified->value,
        ])
        ->assertRedirect(route('tenant.leads.index', ['tenant' => 'acme']))
        ->assertSessionHas('status');

    expect($leadOne->fresh()->status)->toBe(LeadStatus::Qualified)
        ->and($leadTwo->fresh()->status)->toBe(LeadStatus::Qualified);
});

test('tenant users can export selected leads', function () {
    createTestTenant();
    actingAsTenantUser();

    $lead = Lead::factory()->create(['name' => 'Export Lead']);
    Lead::factory()->create(['name' => 'Other Lead']);

    $response = $this->get('/acme/leads-export?lead_ids[]='.$lead->id);

    $response->assertOk();

    expect($response->streamedContent())
        ->toContain('Export Lead')
        ->not->toContain('Other Lead');
});

test('leads index does not show export in the main action bar', function () {
    createTestTenant();
    actingAsTenantUser();

    $this->get('/acme/leads')
        ->assertOk()
        ->assertSee('openBulkModal(\'bulk-assign-leads\')', false)
        ->assertSee('exportSelected()', false)
        ->assertDontSee('href="'.route('tenant.leads.export', ['tenant' => 'acme']).'"', false);
});

test('leads index shows selection checkboxes', function () {
    createTestTenant();
    actingAsTenantUser();

    Lead::factory()->create(['name' => 'Selectable Lead']);

    $this->get('/acme/leads')
        ->assertOk()
        ->assertSee('Select all leads on this page', false)
        ->assertSee('Selectable Lead')
        ->assertSee('Edit Columns')
        ->assertSee('edit-columns', false);
});

test('logging a call activity returns to the lead drawer and opens the dialer', function () {
    createTestTenant();
    actingAsTenantUser();

    $lead = Lead::factory()->create(['phone' => '+91 9000000000']);

    $this->from('/acme/leads?lead='.$lead->id)
        ->post('/acme/leads/'.$lead->id.'/activities', [
            'type' => LeadActivityType::CallMade->value,
            'redirect_url' => 'tel:+919000000000',
        ])
        ->assertRedirect(route('tenant.leads.index', ['tenant' => 'acme', 'lead' => $lead->id]))
        ->assertSessionHas('external_redirect', 'tel:+919000000000');

    expect(LeadActivity::query()->where('lead_id', $lead->id)->where('type', LeadActivityType::CallMade)->exists())->toBeTrue();
});

test('lead drawer actions redirect back to the current page with the lead open', function () {
    createTestTenant();
    actingAsTenantUser();

    $lead = Lead::factory()->create(['name' => 'Activities Lead']);

    $this->from('/acme/activities?filter=today')
        ->post('/acme/leads/'.$lead->id.'/notes', [
            'body' => 'Called from activities.',
        ])
        ->assertRedirect('/acme/activities?filter=today&lead='.$lead->id);
});

test('lead drawer redirect helper appends lead query to previous url', function () {
    expect(LeadDrawerRedirect::appendLeadQuery('https://crm.test/acme/activities?filter=today', 12))
        ->toBe('https://crm.test/acme/activities?filter=today&lead=12');
});

test('converted close can redirect to booking creation', function () {
    createTestTenant();
    actingAsTenantUser();

    $lead = Lead::factory()->create(['status' => LeadStatus::Negotiation]);

    $this->post('/acme/leads/'.$lead->id.'/close', [
        'closing_reason' => LeadClosingReason::Converted->value,
        'continue_to_booking' => '1',
    ])->assertRedirect(route('tenant.bookings.index', ['tenant' => 'acme', 'lead' => $lead->id, 'create' => 1], false));
});
