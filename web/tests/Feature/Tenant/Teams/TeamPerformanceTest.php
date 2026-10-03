<?php

use App\Enums\LeadScheduledEventType;
use App\Enums\LeadStatus;
use App\Enums\TaskStatus;
use App\Models\Lead;
use App\Models\LeadScheduledEvent;
use App\Models\LeadTask;
use App\Models\Role;
use App\Models\SalesTeam;
use App\Models\User;
use Illuminate\Support\Carbon;

test('administrator can view team performance index with ranked cards', function () {
    createTestTenant();
    $admin = actingAsTenantUser();
    $this->travelTo(Carbon::parse('2026-03-15 12:00:00'));

    $agentRole = Role::query()->where('slug', 'agent')->firstOrFail();
    $agent = User::query()->create([
        'name' => 'North Agent',
        'email' => 'north-agent@acme.test',
        'password' => 'password',
        'role_id' => $agentRole->id,
        'email_verified_at' => now(),
    ]);

    $team = SalesTeam::factory()->create(['name' => 'North Sales']);
    $team->members()->attach($agent);

    Lead::factory()->converted()->create([
        'assigned_to_id' => $agent->id,
        'created_at' => Carbon::parse('2026-03-10 09:00:00'),
    ]);
    Lead::factory()->create([
        'status' => LeadStatus::Contacted,
        'assigned_to_id' => $agent->id,
        'created_at' => Carbon::parse('2026-03-11 09:00:00'),
    ]);
    Lead::factory()->create([
        'status' => LeadStatus::Lost,
        'assigned_to_id' => $agent->id,
        'created_at' => Carbon::parse('2026-03-12 09:00:00'),
    ]);

    LeadScheduledEvent::factory()->completed()->create([
        'type' => LeadScheduledEventType::FollowUp,
        'sequence_number' => 1,
        'scheduled_at' => Carbon::parse('2026-03-10 11:00:00'),
        'user_id' => $agent->id,
    ]);
    LeadScheduledEvent::factory()->create([
        'type' => LeadScheduledEventType::FollowUp,
        'sequence_number' => 2,
        'scheduled_at' => Carbon::parse('2026-03-11 11:00:00'),
        'user_id' => $agent->id,
    ]);
    LeadScheduledEvent::factory()->completed()->create([
        'type' => LeadScheduledEventType::SiteVisit,
        'sequence_number' => 1,
        'scheduled_at' => Carbon::parse('2026-03-12 11:00:00'),
        'user_id' => $agent->id,
    ]);

    LeadTask::factory()->create([
        'assigned_to_id' => $agent->id,
        'created_by_id' => $admin->id,
        'status' => TaskStatus::Complete,
        'created_at' => Carbon::parse('2026-03-10 08:00:00'),
    ]);
    LeadTask::factory()->create([
        'assigned_to_id' => $agent->id,
        'created_by_id' => $admin->id,
        'status' => TaskStatus::Pending,
        'created_at' => Carbon::parse('2026-03-11 08:00:00'),
    ]);

    $this->get('/acme/teams/performance')
        ->assertOk()
        ->assertSee('North Sales')
        ->assertSee('#1')
        ->assertSee('Conversion')
        ->assertSee('Follow-up completion')
        ->assertSee('Site visit completion')
        ->assertSee('Task completion')
        ->assertSee('33.3%')
        ->assertSee('50.0%');
});

test('administrator can open a team performance page with member cards', function () {
    createTestTenant();
    actingAsTenantUser();

    $agentRole = Role::query()->where('slug', 'agent')->firstOrFail();
    $agent = User::query()->create([
        'name' => 'Member Agent',
        'email' => 'member-agent@acme.test',
        'password' => 'password',
        'role_id' => $agentRole->id,
        'email_verified_at' => now(),
    ]);

    $team = SalesTeam::factory()->create(['name' => 'West Sales']);
    $team->members()->attach($agent);

    $this->get("/acme/teams/{$team->id}/performance")
        ->assertOk()
        ->assertSee('Member Agent')
        ->assertSee('Back to Performance');
});

test('agent cannot view team performance', function () {
    createTestTenant();
    actingAsTenantUser();

    $agentRole = Role::query()->where('slug', 'agent')->firstOrFail();
    $agent = User::query()->create([
        'name' => 'Blocked Agent',
        'email' => 'blocked-agent@acme.test',
        'password' => 'password',
        'role_id' => $agentRole->id,
        'email_verified_at' => now(),
    ]);

    actingAsTenantUser($agent);

    $this->get('/acme/teams/performance')
        ->assertForbidden();
});
