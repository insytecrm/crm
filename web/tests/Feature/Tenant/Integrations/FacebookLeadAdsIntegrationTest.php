<?php

use App\Actions\StartFacebookOAuth;
use App\Contracts\MetaGraphClient;
use App\Enums\FacebookPageConnectionStatus;
use App\Enums\LeadSource;
use App\Enums\TenantPermission;
use App\Models\FacebookPageConnection;
use App\Models\FacebookPageRegistration;
use App\Models\Lead;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use App\Support\Meta\FakeMetaGraphClient;

test('integrations tab shows a facebook card', function () {
    createTestTenant();
    actingAsTenantUser();

    $this->get('/acme/settings?tab=integrations')
        ->assertOk()
        ->assertSee('Facebook')
        ->assertSee(route('tenant.settings.integrations.facebook.show', ['tenant' => 'acme'], false));
});

test('verify without a page token redirects to facebook login', function () {
    createTestTenant();
    actingAsTenantUser();

    config([
        'services.meta.app_id' => 'meta-app-id',
        'services.meta.app_secret' => 'meta-app-secret',
        'services.meta.oauth_redirect_uri' => 'https://example.test/api/oauth/facebook/callback',
    ]);

    $connection = FacebookPageConnection::factory()->create([
        'created_by_id' => tenantUser()->id,
        'page_id' => '109876543210987',
        'page_access_token' => null,
    ]);

    $response = $this->post("/acme/settings/integrations/facebook/{$connection->id}/verify");

    $response->assertRedirect();
    expect($response->headers->get('Location'))->toStartWith('https://www.facebook.com/v21.0/dialog/oauth');
});

test('authorized users can verify select forms map fields and activate facebook lead ads', function () {
    createTestTenant();
    actingAsTenantUser();

    /** @var FakeMetaGraphClient $meta */
    $meta = app(MetaGraphClient::class);
    $pageId = '109876543210987';
    $meta->seedPage(
        $pageId,
        'Acme Homes',
        [
            ['id' => 'camp_1', 'name' => 'Spring Launch', 'status' => 'ACTIVE'],
        ],
        [
            [
                'id' => 'form_1',
                'name' => 'Website Leads',
                'status' => 'ACTIVE',
                'questions' => [
                    ['key' => 'full_name', 'label' => 'Full Name'],
                    ['key' => 'phone_number', 'label' => 'Phone Number'],
                    ['key' => 'email', 'label' => 'Email'],
                ],
            ],
        ],
    );

    $this->post('/acme/settings/integrations/facebook', [
        'page_id' => $pageId,
    ])->assertRedirect(route('tenant.settings.integrations.facebook.show', ['tenant' => 'acme'], false));

    $connection = FacebookPageConnection::query()->firstOrFail();
    $connection->forceFill(['page_access_token' => 'fake-page-token'])->save();

    expect($connection->status)->toBe(FacebookPageConnectionStatus::Draft)
        ->and($connection->page_id)->toBe($pageId);

    $this->post("/acme/settings/integrations/facebook/{$connection->id}/verify")
        ->assertRedirect('/acme/settings/integrations/facebook')
        ->assertSessionHas('status');

    $connection->refresh();

    expect($connection->status)->toBe(FacebookPageConnectionStatus::Verified)
        ->and($connection->page_name)->toBe('Acme Homes')
        ->and($connection->lead_forms)->not->toBeEmpty();

    $this->put("/acme/settings/integrations/facebook/{$connection->id}/activate", [
        'form_ids' => ['form_1'],
        'field_map' => [
            'name' => 'full_name',
            'phone' => 'phone_number',
            'email' => 'email',
        ],
    ])
        ->assertRedirect('/acme/settings/integrations/facebook')
        ->assertSessionHas('status');

    $connection->refresh();

    expect($connection->status)->toBe(FacebookPageConnectionStatus::Connected)
        ->and($connection->selected_form_ids)->toBe(['form_1']);

    expect(FacebookPageRegistration::query()->where('page_id', $pageId)->where('is_active', true)->exists())->toBeTrue();
});

test('facebook oauth callback stores the page token and verifies the page', function () {
    createTestTenant();
    actingAsTenantUser();

    config([
        'services.meta.app_id' => 'meta-app-id',
        'services.meta.app_secret' => 'meta-app-secret',
        'services.meta.oauth_redirect_uri' => 'https://example.test/api/oauth/facebook/callback',
    ]);

    $pageId = '109876543210987';

    /** @var FakeMetaGraphClient $meta */
    $meta = app(MetaGraphClient::class);
    $meta->seedPage($pageId, 'Acme Homes', [], [
        [
            'id' => 'form_1',
            'name' => 'Website Leads',
            'questions' => [
                ['key' => 'full_name', 'label' => 'Full Name'],
                ['key' => 'phone_number', 'label' => 'Phone Number'],
                ['key' => 'email', 'label' => 'Email'],
            ],
        ],
    ], 'page-token-from-oauth');
    $meta->seedOAuthCode('oauth-code-1', 'user-token-1');

    $connection = FacebookPageConnection::factory()->create([
        'created_by_id' => tenantUser()->id,
        'page_id' => $pageId,
        'page_access_token' => null,
    ]);

    $state = app(StartFacebookOAuth::class)->encodeState([
        'tenant_id' => 'acme',
        'connection_id' => $connection->id,
        'page_id' => $pageId,
    ]);

    tenancy()->end();

    $this->get('/api/oauth/facebook/callback?'.http_build_query([
        'code' => 'oauth-code-1',
        'state' => $state,
    ]))
        ->assertRedirect('/acme/settings/integrations/facebook')
        ->assertSessionHas('status');

    tenancy()->initialize(Tenant::query()->findOrFail('acme'));

    $connection->refresh();

    expect($connection->status)->toBe(FacebookPageConnectionStatus::Verified)
        ->and($connection->page_name)->toBe('Acme Homes')
        ->and($connection->page_access_token)->toBe('page-token-from-oauth');

    tenancy()->end();
});

test('verify fails when the facebook page cannot be accessed', function () {
    createTestTenant();
    actingAsTenantUser();

    /** @var FakeMetaGraphClient $meta */
    $meta = app(MetaGraphClient::class);
    $pageId = '111222333444555';
    $meta->failPage($pageId, 'Could not access this Facebook Page.');

    $connection = FacebookPageConnection::factory()->create([
        'created_by_id' => tenantUser()->id,
        'page_id' => $pageId,
        'page_access_token' => 'fake-page-token',
    ]);

    $this->from('/acme/settings/integrations/facebook')
        ->post("/acme/settings/integrations/facebook/{$connection->id}/verify")
        ->assertRedirect('/acme/settings/integrations/facebook')
        ->assertSessionHasErrors('verify');

    expect($connection->fresh()->status)->toBe(FacebookPageConnectionStatus::Draft);
});

test('meta lead webhook creates a lead for an activated facebook page', function () {
    createTestTenant();
    tenancy()->initialize(Tenant::query()->findOrFail('acme'));

    config([
        'services.meta.verify_token' => 'insyte_meta_verify_local_9f2k7m',
        'services.meta.app_secret' => null,
        'services.meta.page_access_token' => 'fake-page-token',
    ]);

    $pageId = '555666777888999';
    $formId = 'form_99';
    $leadgenId = 'leadgen_abc';

    $connection = FacebookPageConnection::factory()->connected()->create([
        'created_by_id' => tenantUser()->id,
        'page_id' => $pageId,
        'page_name' => 'Acme FB Page',
        'selected_form_ids' => [$formId],
        'field_map' => [
            'name' => 'full_name',
            'phone' => 'phone_number',
            'email' => 'email',
        ],
        'lead_forms' => [
            [
                'id' => $formId,
                'name' => 'Main Form',
                'status' => 'ACTIVE',
                'questions' => [
                    ['key' => 'full_name', 'label' => 'Full Name'],
                    ['key' => 'phone_number', 'label' => 'Phone Number'],
                    ['key' => 'email', 'label' => 'Email'],
                ],
            ],
        ],
    ]);

    FacebookPageRegistration::query()->create([
        'tenant_id' => 'acme',
        'page_id' => $pageId,
        'is_active' => true,
    ]);

    tenancy()->end();

    /** @var FakeMetaGraphClient $meta */
    $meta = app(MetaGraphClient::class);
    $meta->seedLead($leadgenId, $formId, [
        'full_name' => 'Asha Verma',
        'phone_number' => '9988776655',
        'email' => 'asha@example.com',
    ]);

    $this->postJson('/api/webhooks/meta/leads', [
        'object' => 'page',
        'entry' => [
            [
                'id' => $pageId,
                'time' => time(),
                'changes' => [
                    [
                        'field' => 'leadgen',
                        'value' => [
                            'page_id' => $pageId,
                            'form_id' => $formId,
                            'leadgen_id' => $leadgenId,
                        ],
                    ],
                ],
            ],
        ],
    ])
        ->assertOk()
        ->assertSee('EVENT_RECEIVED', false);

    tenancy()->initialize(Tenant::query()->findOrFail('acme'));

    expect(Lead::query()->count())->toBe(1);

    $lead = Lead::query()->firstOrFail();

    expect($lead->name)->toBe('Asha Verma')
        ->and($lead->phone)->toBe('9988776655')
        ->and($lead->email)->toBe('asha@example.com')
        ->and($lead->source)->toBe(LeadSource::Facebook->value)
        ->and($lead->sub_source)->toBe('Acme FB Page › Main Form')
        ->and($lead->source_context)->toMatchArray([
            'segments' => [
                ['key' => 'page', 'id' => $pageId, 'label' => 'Acme FB Page'],
                ['key' => 'form', 'id' => $formId, 'label' => 'Main Form'],
            ],
        ])
        ->and($connection->fresh()->total_synced)->toBe(1);

    tenancy()->end();
});

test('users without integrations manage cannot create a facebook connection', function () {
    createTestTenant();
    tenancy()->initialize(Tenant::query()->findOrFail('acme'));

    $role = Role::query()->create([
        'name' => 'Viewer',
        'slug' => 'viewer-fb',
        'description' => 'View integrations only',
        'is_system' => false,
    ]);

    $viewPermission = Permission::query()->where('key', TenantPermission::IntegrationsView->value)->firstOrFail();
    $role->permissions()->sync([$viewPermission->id]);

    $user = User::query()->create([
        'name' => 'Integration Viewer',
        'email' => 'viewer-fb@acme.test',
        'password' => 'password',
        'role_id' => $role->id,
    ]);

    tenancy()->end();

    $this->actingAs($user)
        ->post('/acme/settings/integrations/facebook', [
            'page_id' => '1234567890',
        ])
        ->assertForbidden();
});
