<?php

use App\Actions\IssueTenantLeadApiToken;
use App\Enums\LeadSource;
use App\Enums\TenantStatus;
use App\Models\Lead;

test('lead ingest api creates a lead with a valid tenant api key', function () {
    $tenant = createTestTenant();
    $token = app(IssueTenantLeadApiToken::class)->handle($tenant);

    $response = $this->postJson('/api/v1/leads', [
        'name' => 'Portal Lead',
        'phone' => '9876543210',
        'email' => 'portal@example.com',
        'source' => '99acres',
    ], [
        'Authorization' => 'Bearer '.$token,
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.name', 'Portal Lead')
        ->assertJsonPath('data.source', LeadSource::Api->value)
        ->assertJsonPath('data.sub_source', '99acres');

    tenancy()->initialize($tenant);

    expect(Lead::query()->where('email', 'portal@example.com')->exists())->toBeTrue();
});

test('lead ingest api defaults source to API when omitted', function () {
    $tenant = createTestTenant();
    $token = app(IssueTenantLeadApiToken::class)->handle($tenant);

    $this->postJson('/api/v1/leads', [
        'name' => 'Nameless Source Lead',
    ], [
        'Authorization' => 'Bearer '.$token,
    ])
        ->assertCreated()
        ->assertJsonPath('data.source', LeadSource::Api->value);
});

test('lead ingest api rejects missing api key', function () {
    createTestTenant();

    $this->postJson('/api/v1/leads', [
        'name' => 'No Key Lead',
    ])->assertUnauthorized();
});

test('lead ingest api rejects invalid api key', function () {
    createTestTenant();

    $this->postJson('/api/v1/leads', [
        'name' => 'Bad Key Lead',
    ], [
        'Authorization' => 'Bearer crm_invalid_token_value_here',
    ])->assertUnauthorized();
});

test('lead ingest api validates required name', function () {
    $tenant = createTestTenant();
    $token = app(IssueTenantLeadApiToken::class)->handle($tenant);

    $this->postJson('/api/v1/leads', [
        'phone' => '9876543210',
    ], [
        'Authorization' => 'Bearer '.$token,
    ])->assertUnprocessable()
        ->assertJsonValidationErrors(['name']);
});

test('lead ingest api rejects suspended tenant keys', function () {
    $tenant = createTestTenant([
        'status' => TenantStatus::Suspended->value,
    ]);
    $token = app(IssueTenantLeadApiToken::class)->handle($tenant);

    $this->postJson('/api/v1/leads', [
        'name' => 'Suspended Lead',
    ], [
        'Authorization' => 'Bearer '.$token,
    ])->assertForbidden();
});

test('lead ingest api isolates leads to the token tenant', function () {
    $acme = createTestTenant();
    $acmeToken = app(IssueTenantLeadApiToken::class)->handle($acme);

    $beta = createTestTenant([
        'slug' => 'beta',
        'name' => 'Beta Inc',
        'email' => 'office@beta.test',
        'admin_email' => 'admin@beta.test',
    ]);
    $betaToken = app(IssueTenantLeadApiToken::class)->handle($beta);

    $this->postJson('/api/v1/leads', [
        'name' => 'Acme Only Lead',
        'email' => 'acme-only@example.com',
    ], [
        'Authorization' => 'Bearer '.$acmeToken,
    ])->assertCreated();

    tenancy()->initialize($acme);
    expect(Lead::query()->where('email', 'acme-only@example.com')->exists())->toBeTrue();

    tenancy()->initialize($beta);
    expect(Lead::query()->where('email', 'acme-only@example.com')->exists())->toBeFalse();

    expect($betaToken)->not->toBe($acmeToken);
});
