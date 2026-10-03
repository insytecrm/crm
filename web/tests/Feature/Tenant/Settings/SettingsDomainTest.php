<?php

use App\Contracts\DnsRecordVerifier;
use App\Enums\DomainPurpose;
use App\Models\Domain;
use App\Models\Property;
use App\Models\Tenant;
use App\Support\Dns\FakeDnsRecordVerifier;

beforeEach(function () {
    $this->dns = new FakeDnsRecordVerifier;
    $this->app->instance(DnsRecordVerifier::class, $this->dns);
});

test('domains settings tab shows crm and website domain forms', function () {
    createTestTenant();
    actingAsTenantUser();

    $this->get('/acme/settings?tab=domains')
        ->assertOk()
        ->assertSee('CRM domain')
        ->assertSee('Website domain')
        ->assertSee('crm.clientdomain.com')
        ->assertSee('www.clientdomain.com');
});

test('tenant admins can save a pending crm domain and see dns instructions', function () {
    createTestTenant();
    actingAsTenantUser();

    $this->from('/acme/settings?tab=domains')
        ->post('/acme/settings/domains', [
            'purpose' => DomainPurpose::Crm->value,
            'domain' => 'crm.acme.test',
        ])
        ->assertRedirect('/acme/settings?tab=domains')
        ->assertSessionHas('status');

    $domain = Domain::query()->where('domain', 'crm.acme.test')->first();

    expect($domain)->not->toBeNull()
        ->and($domain->purpose)->toBe(DomainPurpose::Crm)
        ->and($domain->isVerified())->toBeFalse()
        ->and($domain->verification_token)->not->toBeEmpty();

    $this->get('/acme/settings?tab=domains')
        ->assertOk()
        ->assertSee('Pending DNS')
        ->assertSee($domain->txtRecordValue())
        ->assertSee(config('domains.cname_target'));
});

test('dns verification succeeds when the txt record is present', function () {
    createTestTenant();
    actingAsTenantUser();

    $this->post('/acme/settings/domains', [
        'purpose' => DomainPurpose::Website->value,
        'domain' => 'www.acme.test',
    ])->assertRedirect();

    $domain = Domain::query()->where('domain', 'www.acme.test')->firstOrFail();
    $this->dns->setTxt('www.acme.test', [$domain->txtRecordValue()]);

    $this->from('/acme/settings?tab=domains')
        ->post('/acme/settings/domains/verify', [
            'purpose' => DomainPurpose::Website->value,
        ])
        ->assertRedirect('/acme/settings?tab=domains')
        ->assertSessionHas('status');

    expect($domain->fresh()->isVerified())->toBeTrue();
});

test('dns verification fails when the txt record is missing', function () {
    createTestTenant();
    actingAsTenantUser();

    $this->post('/acme/settings/domains', [
        'purpose' => DomainPurpose::Crm->value,
        'domain' => 'crm.acme.test',
    ])->assertRedirect();

    $this->from('/acme/settings?tab=domains')
        ->post('/acme/settings/domains/verify', [
            'purpose' => DomainPurpose::Crm->value,
        ])
        ->assertRedirect('/acme/settings?tab=domains')
        ->assertSessionHasErrors('domain');
});

test('verified crm domain home redirects into the tenant crm', function () {
    createTestTenant();
    actingAsTenantUser();

    $this->post('/acme/settings/domains', [
        'purpose' => DomainPurpose::Crm->value,
        'domain' => 'crm.acme.test',
    ])->assertRedirect();

    $domain = Domain::query()->where('domain', 'crm.acme.test')->firstOrFail();
    $this->dns->setTxt('crm.acme.test', [$domain->txtRecordValue()]);
    $this->post('/acme/settings/domains/verify', [
        'purpose' => DomainPurpose::Crm->value,
    ])->assertRedirect();

    $this->get('http://crm.acme.test/')
        ->assertRedirect(route('tenant.dashboard', absolute: false));
});

test('verified website domain serves property microsites without the tenant path', function () {
    createTestTenant();
    actingAsTenantUser();

    $this->post('/acme/settings/domains', [
        'purpose' => DomainPurpose::Website->value,
        'domain' => 'www.acme.test',
    ])->assertRedirect();

    $domain = Domain::query()->where('domain', 'www.acme.test')->firstOrFail();
    $this->dns->setTxt('www.acme.test', [$domain->txtRecordValue()]);
    $this->post('/acme/settings/domains/verify', [
        'purpose' => DomainPurpose::Website->value,
    ])->assertRedirect();

    tenancy()->initialize(Tenant::query()->findOrFail('acme'));

    Property::factory()->withMicrosite('the-reserve')->create([
        'project_name' => 'The Reserve',
    ]);

    $this->withoutVite();

    $this->get('http://www.acme.test/projects/the-reserve')
        ->assertOk()
        ->assertSee('The Reserve');
});

test('pending website domain blocks publishing new microsites', function () {
    createTestTenant();
    actingAsTenantUser();

    $this->post('/acme/settings/domains', [
        'purpose' => DomainPurpose::Website->value,
        'domain' => 'www.acme.test',
    ])->assertRedirect();

    $property = Property::factory()->create([
        'project_name' => 'Blocked Project',
        'microsite_enabled' => false,
        'microsite_slug' => null,
    ]);

    $this->from('/acme/properties')
        ->patch('/acme/properties/'.$property->id.'/microsite', [
            'microsite_enabled' => '1',
        ])
        ->assertRedirect('/acme/properties')
        ->assertSessionHasErrors('microsite_enabled');

    expect($property->fresh()->microsite_enabled)->toBeFalse();
});

test('verified website domain unlocks microsite publishing and custom public urls', function () {
    createTestTenant();
    actingAsTenantUser();

    $this->post('/acme/settings/domains', [
        'purpose' => DomainPurpose::Website->value,
        'domain' => 'www.acme.test',
    ])->assertRedirect();

    $domain = Domain::query()->where('domain', 'www.acme.test')->firstOrFail();
    $this->dns->setTxt('www.acme.test', [$domain->txtRecordValue()]);
    $this->post('/acme/settings/domains/verify', [
        'purpose' => DomainPurpose::Website->value,
    ])->assertRedirect();

    $property = Property::factory()->create([
        'project_name' => 'Live Project',
        'microsite_enabled' => false,
        'microsite_slug' => null,
    ]);

    $this->patchJson('/acme/properties/'.$property->id.'/microsite', [
        'microsite_enabled' => '1',
    ])
        ->assertOk()
        ->assertJsonPath('microsite_enabled', true)
        ->assertJsonPath('microsite_url', 'http://www.acme.test/projects/live-project');

    expect($property->fresh()->hasMicrosite())->toBeTrue();
});

test('removing a website domain restores path based microsite publishing', function () {
    createTestTenant();
    actingAsTenantUser();

    $this->post('/acme/settings/domains', [
        'purpose' => DomainPurpose::Website->value,
        'domain' => 'www.acme.test',
    ])->assertRedirect();

    $this->delete('/acme/settings/domains', [
        'purpose' => DomainPurpose::Website->value,
    ])->assertRedirect('/acme/settings?tab=domains');

    $property = Property::factory()->create([
        'project_name' => 'Path Project',
    ]);

    $this->patchJson('/acme/properties/'.$property->id.'/microsite', [
        'microsite_enabled' => '1',
    ])
        ->assertOk()
        ->assertJsonPath('microsite_url', fn ($url) => str_ends_with((string) $url, '/acme/projects/path-project'));
});
