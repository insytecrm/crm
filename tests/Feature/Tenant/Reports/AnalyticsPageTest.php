<?php

use App\Enums\LeadScheduledEventType;
use App\Enums\LeadStatus;
use App\Enums\PropertyType;
use App\Enums\ScheduledActivityOutcome;
use App\Enums\SiteVisitOutcome;
use App\Models\Lead;
use App\Models\LeadScheduledEvent;
use App\Models\Role;
use App\Models\SalesTeam;
use App\Models\User;
use Illuminate\Support\Carbon;

test('analytics page shows kpi cards matching reports style', function () {
    createTestTenant();
    $admin = actingAsTenantUser();
    $this->travelTo(Carbon::parse('2026-03-15 12:00:00'));

    $leadA = Lead::factory()->create([
        'status' => LeadStatus::New,
        'property_type' => PropertyType::Apartment,
        'source' => 'Facebook',
        'lead_score' => 40,
        'assigned_to_id' => $admin->id,
        'created_at' => Carbon::parse('2026-03-14 09:00:00'),
    ]);
    $leadB = Lead::factory()->converted()->create([
        'property_type' => PropertyType::Apartment,
        'source' => 'Facebook',
        'lead_score' => 90,
        'assigned_to_id' => $admin->id,
        'created_at' => Carbon::parse('2026-03-14 10:00:00'),
    ]);
    $leadC = Lead::factory()->create([
        'status' => LeadStatus::Contacted,
        'property_type' => PropertyType::Villa,
        'source' => 'Google',
        'lead_score' => 70,
        'assigned_to_id' => $admin->id,
        'created_at' => Carbon::parse('2026-03-15 08:00:00'),
    ]);

    LeadScheduledEvent::factory()->completed()->create([
        'lead_id' => $leadA->id,
        'type' => LeadScheduledEventType::FollowUp,
        'sequence_number' => 1,
        'scheduled_at' => Carbon::parse('2026-03-14 11:00:00'),
        'completed_at' => Carbon::parse('2026-03-14 11:00:00'),
        'user_id' => $admin->id,
    ]);
    LeadScheduledEvent::factory()->create([
        'lead_id' => $leadB->id,
        'type' => LeadScheduledEventType::FollowUp,
        'sequence_number' => 1,
        'scheduled_at' => Carbon::parse('2026-03-15 09:00:00'),
        'user_id' => $admin->id,
    ]);
    LeadScheduledEvent::factory()->completed()->create([
        'lead_id' => $leadC->id,
        'type' => LeadScheduledEventType::SiteVisit,
        'sequence_number' => 1,
        'scheduled_at' => Carbon::parse('2026-03-15 10:00:00'),
        'completed_at' => Carbon::parse('2026-03-15 11:00:00'),
        'user_id' => $admin->id,
    ]);
    LeadScheduledEvent::factory()->create([
        'lead_id' => $leadC->id,
        'type' => LeadScheduledEventType::SiteVisit,
        'sequence_number' => 2,
        'scheduled_at' => Carbon::parse('2026-03-16 10:00:00'),
        'user_id' => $admin->id,
    ]);

    $this->get('/acme/reports/analytics')
        ->assertOk()
        ->assertDontSee('Coming soon')
        ->assertDontSee('<h1 class="text-2xl font-bold tracking-tight text-black">Analytics</h1>', false)
        ->assertSee('Leads per Day')
        ->assertSee('Conversion Rate')
        ->assertSee('Avg Response Time')
        ->assertSee('Engagement')
        ->assertSee('Follow-up Rate')
        ->assertSee('Site Visits')
        ->assertSee('Filter')
        ->assertSee('Lead Velocity Trend')
        ->assertSee('Lead Aging')
        ->assertSee('Property Type Performance')
        ->assertSee('Source Wise Performance')
        ->assertSee('Source performance view', false)
        ->assertSee('Chart view')
        ->assertSee('Table view')
        ->assertSee('Top Leads')
        ->assertSee('Site Visit Outcomes')
        ->assertSee('Follow-up Outcomes')
        ->assertSee('Lead Score')
        ->assertSee('Facebook')
        ->assertSee('Google')
        ->assertSee('Need Work')
        ->assertSee('Excellent')
        ->assertSee('leadVelocityFill', false)
        ->assertSee('data-report-donut', false)
        ->assertSee('data-lead-hover-card', false)
        ->assertViewHas('metrics', function (array $metrics) use ($leadB, $leadC, $leadA): bool {
            $kpis = $metrics['kpis'];
            $velocity = collect($metrics['lead_velocity']);
            $aging = collect($metrics['lead_aging']);
            $propertyRows = collect($metrics['property_type_performance']);
            $sourceRows = collect($metrics['source_performance']);
            $apartment = $propertyRows->firstWhere('key', PropertyType::Apartment->value);
            $villa = $propertyRows->firstWhere('key', PropertyType::Villa->value);
            $facebook = $sourceRows->firstWhere('key', 'Facebook');
            $google = $sourceRows->firstWhere('key', 'Google');
            $topLeads = $metrics['top_leads'];

            return $kpis['conversion_rate']['value'] === 33.3
                && $kpis['engagement']['value'] === 66.7
                && $kpis['follow_up_rate']['scheduled'] === 2
                && $kpis['follow_up_rate']['completed'] === 1
                && $kpis['follow_up_rate']['value'] === 50.0
                && $kpis['site_visit_rate']['scheduled'] === 2
                && $kpis['site_visit_rate']['completed'] === 1
                && $kpis['site_visit_rate']['value'] === 50.0
                && $kpis['avg_response_time']['value'] === 7200
                && $kpis['leads_per_day']['value'] > 0
                && $velocity->sum('count') === 3
                && (int) $velocity->last()['cumulative'] === 3
                && $aging->sum('count') === 2
                && $aging->contains(fn (array $row): bool => $row['key'] === '0_7' && $row['count'] === 2)
                && $apartment !== null
                && $apartment['total'] === 2
                && $apartment['converted'] === 1
                && $apartment['conversion_rate'] === 50.0
                && $apartment['status'] === 'excellent'
                && $villa !== null
                && $villa['total'] === 1
                && $villa['conversion_rate'] === 0.0
                && $villa['status'] === 'need_work'
                && $propertyRows->count() === count(PropertyType::cases())
                && $propertyRows->contains(fn (array $row): bool => $row['key'] === PropertyType::Plot->value && $row['total'] === 0)
                && $propertyRows->contains(fn (array $row): bool => $row['key'] === PropertyType::Shop->value && $row['total'] === 0)
                && $propertyRows->contains(fn (array $row): bool => $row['key'] === PropertyType::Office->value && $row['total'] === 0)
                && $facebook !== null
                && $facebook['total'] === 2
                && $facebook['converted'] === 1
                && $facebook['conversion_rate'] === 50.0
                && $google !== null
                && $google['total'] === 1
                && $google['active'] === 1
                && $topLeads->count() === 3
                && $topLeads->pluck('id')->all() === [$leadB->id, $leadC->id, $leadA->id]
                && $metrics['site_visit_outcomes']['total'] === 0
                && $metrics['follow_up_outcomes']['total'] === 0
                && count($metrics['site_visit_outcomes']['points']) === 7
                && count($metrics['follow_up_outcomes']['points']) === 6;
        });
});

test('analytics outcome bar charts group completed events by outcome', function () {
    createTestTenant();
    $admin = actingAsTenantUser();
    $this->travelTo(Carbon::parse('2026-03-15 12:00:00'));

    $lead = Lead::factory()->create([
        'assigned_to_id' => $admin->id,
        'created_at' => Carbon::parse('2026-03-10 10:00:00'),
    ]);

    LeadScheduledEvent::factory()->completed()->create([
        'lead_id' => $lead->id,
        'type' => LeadScheduledEventType::SiteVisit,
        'sequence_number' => 1,
        'scheduled_at' => Carbon::parse('2026-03-12 10:00:00'),
        'completed_at' => Carbon::parse('2026-03-12 11:00:00'),
        'completion_outcome' => SiteVisitOutcome::Interested,
        'user_id' => $admin->id,
    ]);
    LeadScheduledEvent::factory()->completed()->create([
        'lead_id' => $lead->id,
        'type' => LeadScheduledEventType::SiteVisit,
        'sequence_number' => 2,
        'scheduled_at' => Carbon::parse('2026-03-13 10:00:00'),
        'completed_at' => Carbon::parse('2026-03-13 11:00:00'),
        'completion_outcome' => SiteVisitOutcome::ReadyToBook,
        'user_id' => $admin->id,
    ]);
    LeadScheduledEvent::factory()->completed()->create([
        'lead_id' => $lead->id,
        'type' => LeadScheduledEventType::SiteVisit,
        'sequence_number' => 3,
        'scheduled_at' => Carbon::parse('2026-03-14 10:00:00'),
        'completed_at' => Carbon::parse('2026-03-14 11:00:00'),
        'completion_outcome' => SiteVisitOutcome::Interested,
        'user_id' => $admin->id,
    ]);

    LeadScheduledEvent::factory()->completed()->create([
        'lead_id' => $lead->id,
        'type' => LeadScheduledEventType::FollowUp,
        'sequence_number' => 1,
        'scheduled_at' => Carbon::parse('2026-03-11 10:00:00'),
        'completed_at' => Carbon::parse('2026-03-11 11:00:00'),
        'completion_outcome' => ScheduledActivityOutcome::Connected,
        'user_id' => $admin->id,
    ]);
    LeadScheduledEvent::factory()->completed()->create([
        'lead_id' => $lead->id,
        'type' => LeadScheduledEventType::FollowUp,
        'sequence_number' => 2,
        'scheduled_at' => Carbon::parse('2026-03-12 09:00:00'),
        'completed_at' => Carbon::parse('2026-03-12 09:30:00'),
        'completion_outcome' => ScheduledActivityOutcome::NoAnswer,
        'user_id' => $admin->id,
    ]);
    LeadScheduledEvent::factory()->create([
        'lead_id' => $lead->id,
        'type' => LeadScheduledEventType::FollowUp,
        'sequence_number' => 3,
        'scheduled_at' => Carbon::parse('2026-03-16 10:00:00'),
        'user_id' => $admin->id,
    ]);

    $this->get('/acme/reports/analytics')
        ->assertOk()
        ->assertSee('Site Visit Outcomes')
        ->assertSee('Follow-up Outcomes')
        ->assertSee('Interested')
        ->assertSee('Ready to book')
        ->assertSee('Connected')
        ->assertSee('No answer')
        ->assertSee('siteVisitOutcomes-0', false)
        ->assertSee('followUpOutcomes-0', false)
        ->assertViewHas('metrics', function (array $metrics): bool {
            $siteVisitPoints = collect($metrics['site_visit_outcomes']['points'])->keyBy('key');
            $followUpPoints = collect($metrics['follow_up_outcomes']['points'])->keyBy('key');

            return $metrics['site_visit_outcomes']['total'] === 3
                && $siteVisitPoints[SiteVisitOutcome::Interested->value]['count'] === 2
                && $siteVisitPoints[SiteVisitOutcome::ReadyToBook->value]['count'] === 1
                && $metrics['follow_up_outcomes']['total'] === 2
                && $followUpPoints[ScheduledActivityOutcome::Connected->value]['count'] === 1
                && $followUpPoints[ScheduledActivityOutcome::NoAnswer->value]['count'] === 1;
        });
});

test('analytics period filter scopes kpi metrics', function () {
    createTestTenant();
    $admin = actingAsTenantUser();
    $this->travelTo(Carbon::parse('2026-03-20 12:00:00'));

    Lead::factory()->converted()->create([
        'assigned_to_id' => $admin->id,
        'created_at' => Carbon::parse('2026-03-10 12:00:00'),
    ]);
    Lead::factory()->create([
        'status' => LeadStatus::New,
        'assigned_to_id' => $admin->id,
        'created_at' => Carbon::parse('2026-02-10 12:00:00'),
    ]);

    $this->get('/acme/reports/analytics?period=this_month')
        ->assertOk()
        ->assertSee('This Month')
        ->assertViewHas('metrics', function (array $metrics): bool {
            return $metrics['kpis']['conversion_rate']['value'] === 100.0
                && $metrics['kpis']['engagement']['value'] === 100.0
                && $metrics['kpis']['leads_per_day']['value'] > 0;
        });
});

test('analytics filter panel includes team and user options', function () {
    createTestTenant();
    $admin = actingAsTenantUser();

    $team = SalesTeam::factory()->create(['name' => 'North Sales']);
    $agent = User::query()->create([
        'name' => 'Filter Agent',
        'email' => 'filter-agent@acme.test',
        'password' => 'password',
        'role_id' => Role::query()->where('slug', 'agent')->value('id'),
        'email_verified_at' => now(),
    ]);
    $team->members()->attach($agent->id);

    $this->get('/acme/reports/analytics')
        ->assertOk()
        ->assertSee('Time period')
        ->assertSee('Team')
        ->assertSee('User')
        ->assertSee('All Teams')
        ->assertSee('All Users')
        ->assertSee('North Sales')
        ->assertSee('Filter Agent')
        ->assertSee($admin->name);
});

test('analytics team and user filters scope kpi metrics', function () {
    createTestTenant();
    $admin = actingAsTenantUser();
    $this->travelTo(Carbon::parse('2026-03-20 12:00:00'));

    $agentRoleId = Role::query()->where('slug', 'agent')->value('id');
    $agentA = User::query()->create([
        'name' => 'Agent A',
        'email' => 'agent-a-analytics@acme.test',
        'password' => 'password',
        'role_id' => $agentRoleId,
        'email_verified_at' => now(),
    ]);
    $agentB = User::query()->create([
        'name' => 'Agent B',
        'email' => 'agent-b-analytics@acme.test',
        'password' => 'password',
        'role_id' => $agentRoleId,
        'email_verified_at' => now(),
    ]);

    $team = SalesTeam::factory()->create(['name' => 'Alpha Team']);
    $team->members()->attach([$agentA->id]);

    Lead::factory()->converted()->create([
        'assigned_to_id' => $agentA->id,
        'created_at' => Carbon::parse('2026-03-10 12:00:00'),
    ]);
    Lead::factory()->create([
        'status' => LeadStatus::New,
        'assigned_to_id' => $agentB->id,
        'created_at' => Carbon::parse('2026-03-11 12:00:00'),
    ]);
    Lead::factory()->converted()->create([
        'assigned_to_id' => $admin->id,
        'created_at' => Carbon::parse('2026-03-12 12:00:00'),
    ]);

    $this->get('/acme/reports/analytics?period=this_month&team='.$team->id)
        ->assertOk()
        ->assertSee('Alpha Team')
        ->assertViewHas('metrics', function (array $metrics): bool {
            return $metrics['kpis']['conversion_rate']['value'] === 100.0
                && $metrics['top_leads']->count() === 1;
        })
        ->assertViewHas('periodFilter', function ($periodFilter) use ($team): bool {
            return $periodFilter->teamId === $team->id
                && $periodFilter->analyticsActiveCount() === 2;
        });

    $this->get('/acme/reports/analytics?period=this_month&user='.$agentB->id)
        ->assertOk()
        ->assertViewHas('metrics', function (array $metrics): bool {
            return $metrics['kpis']['conversion_rate']['value'] === 0.0
                && $metrics['top_leads']->count() === 1;
        })
        ->assertViewHas('periodFilter', function ($periodFilter) use ($agentB): bool {
            return $periodFilter->userId === $agentB->id
                && $periodFilter->analyticsActiveCount() === 2;
        });
});

test('agent cannot view analytics metrics page content', function () {
    createTestTenant();
    actingAsTenantUser();

    $agentRole = Role::query()->where('slug', 'agent')->firstOrFail();
    $agent = User::query()->create([
        'name' => 'Agent User',
        'email' => 'agent-analytics-kpis@acme.test',
        'password' => 'password',
        'role_id' => $agentRole->id,
        'email_verified_at' => now(),
    ]);

    actingAsTenantUser($agent);

    $this->get('/acme/reports/analytics')->assertForbidden();
});
