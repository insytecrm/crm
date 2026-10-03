<?php

use App\Enums\LeadStatus;
use App\Models\Lead;
use App\Models\Role;
use App\Models\User;
use App\Queries\LeadStatistics;

test('lead statistics count each bucket from the current records', function () {
    createTestTenant();
    $user = actingAsTenantUser();
    $this->freezeTime();

    Lead::factory()->create([
        'status' => LeadStatus::New,
        'assigned_to_id' => $user->id,
        'next_follow_up_at' => null,
        'upcoming_site_visit_at' => null,
    ]);
    Lead::factory()->create([
        'status' => LeadStatus::New,
        'assigned_to_id' => $user->id,
        'next_follow_up_at' => null,
        'upcoming_site_visit_at' => null,
    ]);
    Lead::factory()->create([
        'status' => LeadStatus::FollowUp,
        'assigned_to_id' => $user->id,
        'next_follow_up_at' => now()->subHour(),
        'upcoming_site_visit_at' => null,
    ]);
    Lead::factory()->create([
        'status' => LeadStatus::SiteVisit,
        'assigned_to_id' => $user->id,
        'next_follow_up_at' => null,
        'upcoming_site_visit_at' => now()->addDay(),
    ]);
    Lead::factory()->unassigned()->create([
        'status' => LeadStatus::Qualified,
        'next_follow_up_at' => null,
        'upcoming_site_visit_at' => null,
    ]);
    Lead::factory()->converted()->create([
        'assigned_to_id' => $user->id,
        'next_follow_up_at' => now()->subDay(),
        'upcoming_site_visit_at' => null,
    ]);
    Lead::factory()->lost()->create([
        'assigned_to_id' => $user->id,
        'next_follow_up_at' => null,
        'upcoming_site_visit_at' => now()->addDay(),
    ]);

    expect(app(LeadStatistics::class)->forTenant())->toBe([
        'total' => 7,
        'new' => 2,
        'follow_up_due' => 1,
        'site_visits_scheduled' => 2,
        'unassigned' => 1,
        'converted' => 1,
        'lost' => 1,
    ]);
});

test('lead statistics only include leads the signed-in user can see', function () {
    createTestTenant();
    $admin = actingAsTenantUser();

    $agentRole = Role::query()->where('slug', 'agent')->firstOrFail();
    $agent = User::query()->create([
        'name' => 'Stats Agent',
        'email' => 'stats-agent@acme.test',
        'password' => 'password',
        'role_id' => $agentRole->id,
        'email_verified_at' => now(),
    ]);

    Lead::factory()->create([
        'status' => LeadStatus::New,
        'assigned_to_id' => $agent->id,
        'next_follow_up_at' => null,
        'upcoming_site_visit_at' => null,
    ]);
    Lead::factory()->create([
        'status' => LeadStatus::New,
        'assigned_to_id' => $admin->id,
        'next_follow_up_at' => null,
        'upcoming_site_visit_at' => null,
    ]);
    Lead::factory()->unassigned()->create([
        'status' => LeadStatus::Qualified,
        'next_follow_up_at' => null,
        'upcoming_site_visit_at' => null,
    ]);

    actingAsTenantUser($agent);

    expect(app(LeadStatistics::class)->forTenant())->toBe([
        'total' => 1,
        'new' => 1,
        'follow_up_due' => 0,
        'site_visits_scheduled' => 0,
        'unassigned' => 0,
        'converted' => 0,
        'lost' => 0,
    ]);
});
