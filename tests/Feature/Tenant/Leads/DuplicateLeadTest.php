<?php

use App\Enums\LeadActivityType;
use App\Enums\LeadScheduledEventStatus;
use App\Enums\LeadScheduledEventType;
use App\Enums\LeadSource;
use App\Enums\ScheduledActivityOutcome;
use App\Enums\TenantPermission;
use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\LeadScheduledEvent;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;

test('tenant sidebar shows duplicate leads submenu', function () {
    createTestTenant();
    actingAsTenantUser();

    $this->get('/acme/dashboard')
        ->assertOk()
        ->assertSee('Duplicate Leads');
});

test('duplicate leads page lists groups with matching phone numbers', function () {
    createTestTenant();
    actingAsTenantUser();

    Lead::factory()->create(['name' => 'Alice One', 'phone' => '+91 9876543210', 'email' => 'alice1@example.com']);
    Lead::factory()->create(['name' => 'Alice Two', 'phone' => '9876543210', 'email' => 'alice2@example.com']);
    Lead::factory()->create(['name' => 'Unique Lead', 'phone' => '+91 9000000001', 'email' => 'unique@example.com']);

    $this->get('/acme/leads/duplicates')
        ->assertOk()
        ->assertSee('Duplicate Leads')
        ->assertSee('Alice One')
        ->assertSee('Alice Two')
        ->assertSee('Matching phone: 9876543210')
        ->assertDontSee('Unique Lead');
});

test('duplicate leads page lists groups with matching email addresses', function () {
    createTestTenant();
    actingAsTenantUser();

    Lead::factory()->create(['name' => 'Bob One', 'phone' => '+91 9111111111', 'email' => 'Bob@Example.com']);
    Lead::factory()->create(['name' => 'Bob Two', 'phone' => '+91 9222222222', 'email' => 'bob@example.com']);

    $this->get('/acme/leads/duplicates')
        ->assertOk()
        ->assertSee('Bob One')
        ->assertSee('Bob Two')
        ->assertSee('Matching email: bob@example.com');
});

test('duplicate leads page connects groups linked by phone and email', function () {
    createTestTenant();
    actingAsTenantUser();

    Lead::factory()->create(['name' => 'Chain One', 'phone' => '+91 9333333333', 'email' => 'chain-one@example.com']);
    Lead::factory()->create(['name' => 'Chain Two', 'phone' => '+91 9333333333', 'email' => 'shared@example.com']);
    Lead::factory()->create(['name' => 'Chain Three', 'phone' => '+91 9444444444', 'email' => 'shared@example.com']);

    $this->get('/acme/leads/duplicates')
        ->assertOk()
        ->assertSee('Chain One')
        ->assertSee('Chain Two')
        ->assertSee('Chain Three');
});

test('duplicate leads page shows empty state when no duplicates exist', function () {
    createTestTenant();
    actingAsTenantUser();

    Lead::factory()->create(['name' => 'Only Lead', 'phone' => '+91 9555555555', 'email' => 'only@example.com']);

    $this->get('/acme/leads/duplicates')
        ->assertOk()
        ->assertSee('No duplicate leads found.');
});

test('tenant users can merge duplicate leads into a primary lead', function () {
    createTestTenant();
    actingAsTenantUser();

    $primary = Lead::factory()->create([
        'name' => 'Primary Lead',
        'phone' => '+91 9666666666',
        'email' => null,
        'source' => null,
        'lead_score' => 0,
        'lead_score_intent' => 0,
    ]);

    $duplicate = Lead::factory()->create([
        'name' => 'Duplicate Lead',
        'phone' => '9666666666',
        'email' => 'duplicate@example.com',
        'source' => LeadSource::Referral->value,
        'lead_score' => 0,
        'lead_score_intent' => 0,
    ]);

    LeadScheduledEvent::factory()->create([
        'lead_id' => $duplicate->id,
        'type' => LeadScheduledEventType::FollowUp,
        'sequence_number' => 1,
        'status' => LeadScheduledEventStatus::Completed,
        'completed_at' => now(),
        'completion_outcome' => ScheduledActivityOutcome::ReadyToVisit->value,
    ]);

    LeadActivity::factory()->create([
        'lead_id' => $duplicate->id,
        'type' => LeadActivityType::NoteAdded,
        'description' => 'Duplicate note',
    ]);

    $this->post('/acme/leads/duplicates/merge', [
        'primary_lead_id' => $primary->id,
        'duplicate_lead_ids' => [$duplicate->id],
    ])
        ->assertRedirect(route('tenant.leads.duplicates.index', ['tenant' => 'acme']))
        ->assertSessionHas('status');

    $primary->refresh();

    expect($primary->email)->toBe('duplicate@example.com')
        ->and($primary->source)->toBe(LeadSource::Referral->value)
        ->and($primary->lead_score_intent)->toBe(60)
        ->and(Lead::query()->find($duplicate->id))->toBeNull()
        ->and(LeadActivity::query()->where('lead_id', $primary->id)->count())->toBe(2)
        ->and(LeadActivity::query()->where('lead_id', $primary->id)->where('type', LeadActivityType::LeadMerged)->exists())->toBeTrue();
});

test('merging duplicate leads remaps overlapping scheduled event sequences', function () {
    createTestTenant();
    actingAsTenantUser();

    $primary = Lead::factory()->create([
        'name' => 'Primary Lead',
        'phone' => '+91 9666666666',
        'next_follow_up_at' => now()->addDays(2),
    ]);

    $duplicate = Lead::factory()->create([
        'name' => 'Duplicate Lead',
        'phone' => '9666666666',
        'next_follow_up_at' => now()->addDay(),
    ]);

    LeadScheduledEvent::factory()->create([
        'lead_id' => $primary->id,
        'type' => LeadScheduledEventType::FollowUp,
        'sequence_number' => 1,
        'status' => LeadScheduledEventStatus::Scheduled,
        'scheduled_at' => now()->addDays(2),
    ]);

    LeadScheduledEvent::factory()->create([
        'lead_id' => $duplicate->id,
        'type' => LeadScheduledEventType::FollowUp,
        'sequence_number' => 1,
        'status' => LeadScheduledEventStatus::Scheduled,
        'scheduled_at' => now()->addDay(),
    ]);

    $this->post('/acme/leads/duplicates/merge', [
        'primary_lead_id' => $primary->id,
        'duplicate_lead_ids' => [$duplicate->id],
    ])
        ->assertRedirect(route('tenant.leads.duplicates.index', ['tenant' => 'acme']))
        ->assertSessionHas('status');

    $events = LeadScheduledEvent::query()
        ->where('lead_id', $primary->id)
        ->where('type', LeadScheduledEventType::FollowUp)
        ->orderBy('sequence_number')
        ->get();

    expect($events)->toHaveCount(2)
        ->and($events->pluck('sequence_number')->all())->toBe([1, 2])
        ->and(Lead::query()->find($duplicate->id))->toBeNull();
});

test('users without lead update permission cannot merge duplicate leads', function () {
    createTestTenant();
    actingAsTenantUser();

    $primary = Lead::factory()->create(['phone' => '+91 9777777777']);
    $duplicate = Lead::factory()->create(['phone' => '9777777777']);

    $readOnlyRole = Role::query()->create([
        'name' => 'Read Only',
        'slug' => 'read-only',
        'description' => 'View leads only',
        'is_system' => false,
    ]);

    $readOnlyRole->permissions()->sync(
        Permission::query()
            ->where('key', TenantPermission::LeadsView->value)
            ->pluck('id'),
    );

    $viewer = User::query()->create([
        'name' => 'Viewer User',
        'email' => 'viewer@acme.test',
        'password' => 'password',
        'role_id' => $readOnlyRole->id,
        'email_verified_at' => now(),
    ]);

    actingAsTenantUser($viewer);

    $this->post('/acme/leads/duplicates/merge', [
        'primary_lead_id' => $primary->id,
        'duplicate_lead_ids' => [$duplicate->id],
    ])->assertForbidden();

    expect(Lead::withoutGlobalScopes()->find($duplicate->id))->not->toBeNull();
});
