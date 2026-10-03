<?php

use App\Actions\EnsurePortalWebhookEndpoint;
use App\Actions\IssuePortalWebhookSecret;
use App\Enums\PropertyPortal;
use App\Enums\TenantStatus;
use App\Models\Lead;

test('portal webhook creates a lead for the matching tenant', function (PropertyPortal $portal) {
    $tenant = createTestTenant();
    $endpoint = app(EnsurePortalWebhookEndpoint::class)->handle($tenant, $portal);
    $secret = $endpoint->plainTextSecret();

    $this->postJson($endpoint->webhookUrl(), [
        'name' => $portal->label().' Lead',
        'phone' => '9876543210',
        'email' => $portal->value.'@example.com',
    ], [
        'Authorization' => 'Bearer '.$secret,
    ])
        ->assertCreated()
        ->assertJsonPath('data.source', $portal->leadSource()->value)
        ->assertJsonPath('data.name', $portal->label().' Lead');

    tenancy()->initialize($tenant);

    expect(Lead::query()->where('email', $portal->value.'@example.com')->exists())->toBeTrue();
})->with(PropertyPortal::cases());

test('portal webhook accepts alternate field names and x-webhook-secret header', function () {
    $tenant = createTestTenant();
    $endpoint = app(EnsurePortalWebhookEndpoint::class)->handle($tenant, PropertyPortal::Housing);
    $secret = $endpoint->plainTextSecret();

    $this->postJson($endpoint->webhookUrl(), [
        'Name' => 'Housing Buyer',
        'Mobile' => '9998887777',
        'Email' => 'housing-buyer@example.com',
        'City' => 'Pune',
    ], [
        'X-Webhook-Secret' => $secret,
    ])
        ->assertCreated()
        ->assertJsonPath('data.name', 'Housing Buyer')
        ->assertJsonPath('data.phone', '9998887777')
        ->assertJsonPath('data.location', 'Pune')
        ->assertJsonPath('data.source', PropertyPortal::Housing->leadSource()->value);
});

test('portal webhook rejects invalid secret', function () {
    $tenant = createTestTenant();
    $endpoint = app(EnsurePortalWebhookEndpoint::class)->handle($tenant, PropertyPortal::MagicBricks);

    $this->postJson($endpoint->webhookUrl(), [
        'name' => 'Bad Secret Lead',
    ], [
        'Authorization' => 'Bearer wrong-secret',
    ])->assertUnauthorized();
});

test('portal webhook returns not found for unknown webhook id', function () {
    createTestTenant();

    $this->postJson('/api/webhooks/99acres/wh_doesnotexist0000000000', [
        'name' => 'Ghost Lead',
    ], [
        'Authorization' => 'Bearer whsec_anything',
    ])->assertNotFound();
});

test('portal webhook rejects suspended tenants', function () {
    $tenant = createTestTenant([
        'status' => TenantStatus::Suspended->value,
    ]);
    $endpoint = app(EnsurePortalWebhookEndpoint::class)->handle($tenant, PropertyPortal::NoBroker);

    $this->postJson($endpoint->webhookUrl(), [
        'name' => 'Suspended Portal Lead',
    ], [
        'Authorization' => 'Bearer '.$endpoint->plainTextSecret(),
    ])->assertForbidden();
});

test('portal webhook uses different urls per portal', function () {
    $tenant = createTestTenant();
    $housing = app(EnsurePortalWebhookEndpoint::class)->handle($tenant, PropertyPortal::Housing);
    $magic = app(EnsurePortalWebhookEndpoint::class)->handle($tenant, PropertyPortal::MagicBricks);

    expect($housing->webhookUrl())->toContain('/api/webhooks/housing/'.$housing->webhook_id)
        ->and($magic->webhookUrl())->toContain('/api/webhooks/magicbricks/'.$magic->webhook_id)
        ->and($housing->webhook_id)->not->toBe($magic->webhook_id);
});

test('portal webhook mismatched portal path returns not found', function () {
    $tenant = createTestTenant();
    $endpoint = app(EnsurePortalWebhookEndpoint::class)->handle($tenant, PropertyPortal::Housing);

    $this->postJson('/api/webhooks/magicbricks/'.$endpoint->webhook_id, [
        'name' => 'Wrong Portal Path',
    ], [
        'Authorization' => 'Bearer '.$endpoint->plainTextSecret(),
    ])->assertNotFound();
});

test('regenerated portal webhook secret invalidates the old secret', function () {
    $tenant = createTestTenant();
    $endpoint = app(EnsurePortalWebhookEndpoint::class)->handle($tenant, PropertyPortal::NinetyNineAcres);
    $oldSecret = $endpoint->plainTextSecret();

    app(IssuePortalWebhookSecret::class)->handle($endpoint->fresh());
    $endpoint->refresh();

    $this->postJson($endpoint->webhookUrl(), [
        'name' => 'Old Secret Lead',
    ], [
        'Authorization' => 'Bearer '.$oldSecret,
    ])->assertUnauthorized();

    $this->postJson($endpoint->webhookUrl(), [
        'name' => 'New Secret Lead',
    ], [
        'Authorization' => 'Bearer '.$endpoint->plainTextSecret(),
    ])->assertCreated();
});
