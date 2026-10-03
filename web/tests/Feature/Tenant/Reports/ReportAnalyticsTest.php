<?php

use App\Enums\LeadScheduledEventType;
use App\Enums\LeadStatus;
use App\Enums\PropertyType;
use App\Enums\TaskStatus;
use App\Models\Lead;
use App\Models\LeadScheduledEvent;
use App\Models\LeadTask;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Carbon;

test('guests are redirected from reports page', function () {
    createTestTenant();

    $this->get('/acme/reports')
        ->assertRedirect(route('tenant.login', ['tenant' => 'acme']));
});

test('guests are redirected from analytics page', function () {
    createTestTenant();

    $this->get('/acme/reports/analytics')
        ->assertRedirect(route('tenant.login', ['tenant' => 'acme']));
});

test('agent cannot view reports page', function () {
    createTestTenant();
    actingAsTenantUser();

    $agentRole = Role::query()->where('slug', 'agent')->firstOrFail();
    $agent = User::query()->create([
        'name' => 'Agent User',
        'email' => 'agent-reports@acme.test',
        'password' => 'password',
        'role_id' => $agentRole->id,
        'email_verified_at' => now(),
    ]);

    actingAsTenantUser($agent);

    $this->get('/acme/reports')
        ->assertForbidden();
});

test('agent cannot view analytics page', function () {
    createTestTenant();
    actingAsTenantUser();

    $agentRole = Role::query()->where('slug', 'agent')->firstOrFail();
    $agent = User::query()->create([
        'name' => 'Agent User',
        'email' => 'agent-analytics@acme.test',
        'password' => 'password',
        'role_id' => $agentRole->id,
        'email_verified_at' => now(),
    ]);

    actingAsTenantUser($agent);

    $this->get('/acme/reports/analytics')
        ->assertForbidden();
});

test('agent sidebar does not include reports', function () {
    createTestTenant();
    actingAsTenantUser();

    $agentRole = Role::query()->where('slug', 'agent')->firstOrFail();
    $agent = User::query()->create([
        'name' => 'Agent User',
        'email' => 'agent-reports-nav@acme.test',
        'password' => 'password',
        'role_id' => $agentRole->id,
        'email_verified_at' => now(),
    ]);

    actingAsTenantUser($agent);

    $this->get('/acme/dashboard')
        ->assertOk()
        ->assertDontSee(route('tenant.reports.index', ['tenant' => 'acme'], false))
        ->assertDontSee(route('tenant.reports.analytics', ['tenant' => 'acme'], false));
});

test('reports page shows kpi cards charts and toolbar actions', function () {
    createTestTenant();
    $admin = actingAsTenantUser();
    $this->travelTo(Carbon::parse('2026-03-15 10:00:00'));

    $newLead = Lead::factory()->create([
        'status' => LeadStatus::New,
        'property_type' => PropertyType::Apartment,
        'assigned_to_id' => $admin->id,
        'created_at' => now(),
    ]);
    Lead::factory()->converted()->create([
        'property_type' => PropertyType::Villa,
        'assigned_to_id' => $admin->id,
        'created_at' => now(),
    ]);
    Lead::factory()->lost()->create([
        'property_type' => PropertyType::Plot,
        'assigned_to_id' => $admin->id,
        'created_at' => now(),
    ]);
    Lead::factory()->create([
        'status' => LeadStatus::Qualified,
        'property_type' => PropertyType::Apartment,
        'assigned_to_id' => $admin->id,
        'created_at' => now(),
    ]);

    LeadScheduledEvent::factory()->create([
        'lead_id' => $newLead->id,
        'scheduled_at' => now(),
    ]);
    LeadScheduledEvent::factory()->create([
        'lead_id' => $newLead->id,
        'sequence_number' => 2,
        'scheduled_at' => now()->subMonth(),
    ]);

    $this->get('/acme/reports')
        ->assertOk()
        ->assertDontSee('<h1 class="text-2xl font-bold tracking-tight text-black">Reports</h1>', false)
        ->assertSee('Total Leads')
        ->assertSee('Converted Leads')
        ->assertSee('Active')
        ->assertSee('Total Activities')
        ->assertSee('Today’s Activities')
        ->assertSee('Lost Leads')
        ->assertSee('New Leads')
        ->assertSee('Activity Trend')
        ->assertSee('Leads by Status')
        ->assertSee('Leads by Property Type')
        ->assertSee('Leads by User')
        ->assertSee('Agent Performance')
        ->assertSee('Conversion Ratio')
        ->assertSee('Site Visits')
        ->assertSee('Follow-ups')
        ->assertSee('Tasks')
        ->assertSee('Apartment')
        ->assertSee($admin->name)
        ->assertSee('newLeadsBarFill', false)
        ->assertSee('activityTrendStroke', false)
        ->assertSee('data-report-donut', false)
        ->assertSee('Export')
        ->assertSee('Print')
        ->assertSee('Filter')
        ->assertDontSee('Filter chart')
        ->assertSee(route('tenant.reports.export', ['tenant' => 'acme'], false), false)
        ->assertSee(route('tenant.reports.print', ['tenant' => 'acme'], false), false)
        ->assertViewHas('analytics', fn (array $analytics): bool => $analytics['kpis'] === [
            'total_leads' => 4,
            'converted_leads' => 1,
            'active_leads' => 2,
            'total_activities' => 2,
            'todays_activities' => 1,
            'lost_leads' => 1,
        ]
            && collect($analytics['new_leads'])->sum('count') === 4
            && collect($analytics['activity_trend'])->sum('count') === 2
            && collect($analytics['by_status'])->sum('count') === 4
            && count($analytics['agent_performance']) === 1
            && $analytics['agent_performance'][0]['agent'] === $admin->name
            && $analytics['agent_performance'][0]['total_leads'] === 4
            && $analytics['agent_performance'][0]['converted'] === 1
            && $analytics['agent_performance'][0]['lost'] === 1
            && $analytics['agent_performance'][0]['active'] === 2
            && $analytics['agent_performance'][0]['conversion_ratio'] === 25.0
            && $analytics['site_visits']['total'] === 0
            && $analytics['follow_ups']['total'] === 2
            && $analytics['tasks']['total'] === 0);
});

test('reports activity summary cards show scheduled overdue completed and task statuses', function () {
    createTestTenant();
    $admin = actingAsTenantUser();
    $this->travelTo(Carbon::parse('2026-03-15 12:00:00'));

    $lead = Lead::factory()->create([
        'assigned_to_id' => $admin->id,
        'created_at' => Carbon::parse('2026-03-10 10:00:00'),
    ]);

    LeadScheduledEvent::factory()->create([
        'lead_id' => $lead->id,
        'type' => LeadScheduledEventType::SiteVisit,
        'sequence_number' => 1,
        'scheduled_at' => Carbon::parse('2026-03-20 10:00:00'),
    ]);
    LeadScheduledEvent::factory()->create([
        'lead_id' => $lead->id,
        'type' => LeadScheduledEventType::SiteVisit,
        'sequence_number' => 2,
        'scheduled_at' => Carbon::parse('2026-03-10 10:00:00'),
    ]);
    LeadScheduledEvent::factory()->completed()->create([
        'lead_id' => $lead->id,
        'type' => LeadScheduledEventType::SiteVisit,
        'sequence_number' => 3,
        'scheduled_at' => Carbon::parse('2026-03-12 10:00:00'),
        'completed_at' => Carbon::parse('2026-03-12 11:00:00'),
    ]);

    LeadScheduledEvent::factory()->create([
        'lead_id' => $lead->id,
        'type' => LeadScheduledEventType::FollowUp,
        'sequence_number' => 1,
        'scheduled_at' => Carbon::parse('2026-03-18 10:00:00'),
    ]);
    LeadScheduledEvent::factory()->completed()->create([
        'lead_id' => $lead->id,
        'type' => LeadScheduledEventType::FollowUp,
        'sequence_number' => 2,
        'scheduled_at' => Carbon::parse('2026-03-11 10:00:00'),
        'completed_at' => Carbon::parse('2026-03-11 12:00:00'),
    ]);

    LeadTask::factory()->create([
        'lead_id' => $lead->id,
        'assigned_to_id' => $admin->id,
        'created_by_id' => $admin->id,
        'status' => TaskStatus::Pending,
        'created_at' => Carbon::parse('2026-03-10 09:00:00'),
    ]);
    LeadTask::factory()->inProgress()->create([
        'lead_id' => $lead->id,
        'assigned_to_id' => $admin->id,
        'created_by_id' => $admin->id,
        'created_at' => Carbon::parse('2026-03-11 09:00:00'),
    ]);
    LeadTask::factory()->complete()->create([
        'lead_id' => $lead->id,
        'assigned_to_id' => $admin->id,
        'created_by_id' => $admin->id,
        'created_at' => Carbon::parse('2026-03-12 09:00:00'),
    ]);
    LeadTask::factory()->cancelled()->create([
        'lead_id' => $lead->id,
        'assigned_to_id' => $admin->id,
        'created_by_id' => $admin->id,
        'created_at' => Carbon::parse('2026-03-13 09:00:00'),
    ]);

    $this->get('/acme/reports')
        ->assertOk()
        ->assertSee('Site Visits')
        ->assertSee('Follow-ups')
        ->assertSee('Tasks')
        ->assertSee('Scheduled')
        ->assertSee('Overdue')
        ->assertSee('Pending')
        ->assertSee('In Progress')
        ->assertSee('Completion')
        ->assertViewHas('analytics', function (array $analytics): bool {
            $siteVisits = collect($analytics['site_visits']['statuses'])->keyBy('key');
            $followUps = collect($analytics['follow_ups']['statuses'])->keyBy('key');
            $tasks = collect($analytics['tasks']['statuses'])->keyBy('key');

            return $analytics['site_visits']['total'] === 3
                && $siteVisits['scheduled']['count'] === 1
                && $siteVisits['overdue']['count'] === 1
                && $siteVisits['completed']['count'] === 1
                && $analytics['site_visits']['completion_rate'] === 33.3
                && $analytics['follow_ups']['total'] === 2
                && $followUps['scheduled']['count'] === 1
                && $followUps['overdue']['count'] === 0
                && $followUps['completed']['count'] === 1
                && $analytics['follow_ups']['completion_rate'] === 50.0
                && $analytics['tasks']['total'] === 3
                && $tasks['pending']['count'] === 1
                && $tasks['in_progress']['count'] === 1
                && $tasks['completed']['count'] === 1
                && $analytics['tasks']['completion_rate'] === 33.3;
        });
});

test('reports period filter applies to all charts and kpis', function () {
    createTestTenant();
    $admin = actingAsTenantUser();
    $this->travelTo(Carbon::parse('2026-03-20 10:00:00'));

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

    $this->get('/acme/reports?period=this_month')
        ->assertOk()
        ->assertSee('This Month')
        ->assertSee('Apartment')
        ->assertDontSee('Villa')
        ->assertSee(route('tenant.reports.export', ['tenant' => 'acme', 'period' => 'this_month'], false), false)
        ->assertSee(route('tenant.reports.print', ['tenant' => 'acme', 'period' => 'this_month'], false), false)
        ->assertViewHas('analytics', function (array $analytics): bool {
            return $analytics['kpis']['total_leads'] === 1
                && collect($analytics['by_status'])->sum('count') === 1
                && collect($analytics['by_property_type'])->sum('count') === 1
                && count($analytics['agent_performance']) === 1
                && $analytics['agent_performance'][0]['total_leads'] === 1;
        });
});

test('manager reports include leads assigned to other users', function () {
    createTestTenant();
    $admin = actingAsTenantUser();

    $managerRole = Role::query()->where('slug', 'manager')->firstOrFail();
    $manager = User::query()->create([
        'name' => 'Reports Manager',
        'email' => 'manager-reports@acme.test',
        'password' => 'password',
        'role_id' => $managerRole->id,
        'email_verified_at' => now(),
    ]);

    Lead::factory()->create([
        'assigned_to_id' => $admin->id,
        'property_type' => PropertyType::Shop,
        'created_at' => now(),
    ]);

    actingAsTenantUser($manager);

    $this->get('/acme/reports')
        ->assertOk()
        ->assertSee('Shop')
        ->assertSee($admin->name)
        ->assertViewHas('analytics', fn (array $analytics): bool => $analytics['kpis']['total_leads'] === 1);
});

test('analytics page is no longer a placeholder', function () {
    createTestTenant();
    actingAsTenantUser();

    $this->get('/acme/reports/analytics')
        ->assertOk()
        ->assertSee('Leads per Day')
        ->assertSee('Conversion Rate')
        ->assertDontSee('Coming soon')
        ->assertDontSee('Leads by Status')
        ->assertDontSee('Agent Performance');
});

test('reports page escapes dangerous user names', function () {
    createTestTenant();
    actingAsTenantUser();

    $agentRole = Role::query()->where('slug', 'agent')->firstOrFail();
    $agent = User::query()->create([
        'name' => '<script>alert("xss")</script>',
        'email' => 'xss-agent@acme.test',
        'password' => 'password',
        'role_id' => $agentRole->id,
        'email_verified_at' => now(),
    ]);

    Lead::factory()->create([
        'assigned_to_id' => $agent->id,
        'created_at' => now(),
    ]);

    $this->get('/acme/reports')
        ->assertOk()
        ->assertSee('\u003Cscript\u003E', false)
        ->assertDontSee('<script>alert("xss")</script>', false);
});
