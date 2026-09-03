<?php

use App\Actions\CreateTenant;
use App\Enums\LeadScheduledEventStatus;
use App\Enums\LeadScheduledEventType;
use App\Enums\TenantStatus;
use App\Models\Lead;
use App\Models\LeadScheduledEvent;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind a different classes or traits.
|
*/

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function something()
{
    // ..
}

function createTestTenant(array $overrides = []): Tenant
{
    return app(CreateTenant::class)->handle(array_merge([
        'slug' => 'acme',
        'name' => 'Acme Inc',
        'email' => 'office@acme.test',
        'status' => TenantStatus::Active->value,
        'admin_name' => 'Acme Admin',
        'admin_email' => 'admin@acme.test',
        'admin_password' => 'password',
    ], $overrides));
}

function tenantUser(string $email = 'admin@acme.test'): User
{
    return User::query()->where('email', $email)->firstOrFail();
}

function actingAsTenantUser(?User $user = null): User
{
    $tenant = Tenant::query()->findOrFail('acme');
    tenancy()->initialize($tenant);

    $user ??= tenantUser();

    test()->actingAs($user);

    return $user;
}

function scheduleFollowUpForLead(Lead $lead, array $overrides = []): LeadScheduledEvent
{
    return LeadScheduledEvent::factory()->create(array_merge([
        'lead_id' => $lead->id,
        'type' => LeadScheduledEventType::FollowUp,
        'sequence_number' => 1,
        'scheduled_at' => $lead->next_follow_up_at ?? now()->addHour(),
        'status' => LeadScheduledEventStatus::Scheduled,
    ], $overrides));
}

function scheduleSiteVisitForLead(Lead $lead, array $overrides = []): LeadScheduledEvent
{
    return LeadScheduledEvent::factory()->create(array_merge([
        'lead_id' => $lead->id,
        'type' => LeadScheduledEventType::SiteVisit,
        'sequence_number' => 1,
        'scheduled_at' => $lead->upcoming_site_visit_at ?? now()->addDay(),
        'status' => LeadScheduledEventStatus::Scheduled,
    ], $overrides));
}
