<?php

use App\Contracts\GoogleSheetsClient;
use App\Enums\GoogleSheetConnectionStatus;
use App\Enums\LeadSource;
use App\Enums\TenantPermission;
use App\Models\GoogleSheetConnection;
use App\Models\Lead;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use App\Support\GoogleSheets\FakeGoogleSheetsClient;

test('integrations tab shows a google sheets card', function () {
    createTestTenant();
    actingAsTenantUser();

    $this->get('/acme/settings?tab=integrations')
        ->assertOk()
        ->assertSee('Google Sheets')
        ->assertSee(route('tenant.settings.integrations.google-sheets.index', ['tenant' => 'acme'], false));
});

test('google sheets index uses the dashboard layout', function () {
    createTestTenant();
    actingAsTenantUser();

    GoogleSheetConnection::factory()->connected()->create([
        'created_by_id' => tenantUser()->id,
        'name' => 'DEMO LEAD INTEGRATION',
        'total_synced' => 24,
        'total_skipped' => 0,
        'total_failed' => 0,
        'last_synced_at' => now()->subMinutes(9),
    ]);

    $this->get('/acme/settings/integrations/google-sheets')
        ->assertOk()
        ->assertSee('Google Sheets')
        ->assertSee('Add New Sheet')
        ->assertSee('Total Sheets')
        ->assertSee('Total Synced')
        ->assertSee('Sync All Active Sheets')
        ->assertSee('All Sheets')
        ->assertSee('DEMO LEAD INTEGRATION')
        ->assertSee('Active')
        ->assertSee('24');
});

test('authorized users can create verify map and connect a google sheet that imports leads', function () {
    createTestTenant();
    actingAsTenantUser();

    /** @var FakeGoogleSheetsClient $sheets */
    $sheets = app(GoogleSheetsClient::class);
    $spreadsheetId = '1AbcGoogleSheetIntegrationTestIdXXXX';
    $sheets->seed(
        $spreadsheetId,
        ['Full Name', 'Mobile', 'Email'],
        [
            ['Riya Sharma', '9876543210', 'riya@example.com'],
            ['', '1111111111', 'skip@example.com'],
        ],
    );

    $this->post('/acme/settings/integrations/google-sheets', [
        'name' => 'Website form',
        'spreadsheet_url' => "https://docs.google.com/spreadsheets/d/{$spreadsheetId}/edit#gid=0",
    ])->assertRedirect();

    $connection = GoogleSheetConnection::query()->firstOrFail();

    expect($connection->status)->toBe(GoogleSheetConnectionStatus::Draft)
        ->and($connection->spreadsheet_id)->toBe($spreadsheetId);

    $this->post("/acme/settings/integrations/google-sheets/{$connection->id}/verify")
        ->assertRedirect("/acme/settings/integrations/google-sheets/{$connection->id}")
        ->assertSessionHas('status');

    $connection->refresh();

    expect($connection->status)->toBe(GoogleSheetConnectionStatus::Verified)
        ->and($connection->headers)->toBe(['Full Name', 'Mobile', 'Email']);

    $this->put("/acme/settings/integrations/google-sheets/{$connection->id}/connect", [
        'column_map' => [
            'name' => 'Full Name',
            'phone' => 'Mobile',
            'email' => 'Email',
        ],
    ])
        ->assertRedirect("/acme/settings/integrations/google-sheets/{$connection->id}")
        ->assertSessionHas('status');

    $connection->refresh();

    expect($connection->status)->toBe(GoogleSheetConnectionStatus::Connected)
        ->and($connection->last_synced_row)->toBe(3)
        ->and($connection->total_synced)->toBe(1)
        ->and($connection->total_skipped)->toBe(1)
        ->and(Lead::query()->count())->toBe(1);

    $lead = Lead::query()->firstOrFail();

    expect($lead->name)->toBe('Riya Sharma')
        ->and($lead->phone)->toBe('9876543210')
        ->and($lead->email)->toBe('riya@example.com')
        ->and($lead->source)->toBe(LeadSource::GoogleSheets->value)
        ->and($lead->sub_source)->toBe('Website form')
        ->and($lead->source_context)->toMatchArray([
            'segments' => [
                ['key' => 'connection', 'id' => null, 'label' => 'Website form'],
            ],
        ]);
});

test('verify fails when the sheet cannot be read', function () {
    createTestTenant();
    actingAsTenantUser();

    /** @var FakeGoogleSheetsClient $sheets */
    $sheets = app(GoogleSheetsClient::class);
    $spreadsheetId = '1MissingSheetXXXXXXXXXXXXXXXXXXXX';
    $sheets->fail($spreadsheetId, 'Could not access this Google Sheet.');

    $connection = GoogleSheetConnection::factory()->create([
        'created_by_id' => tenantUser()->id,
        'spreadsheet_id' => $spreadsheetId,
        'spreadsheet_url' => "https://docs.google.com/spreadsheets/d/{$spreadsheetId}/edit",
    ]);

    $this->from("/acme/settings/integrations/google-sheets/{$connection->id}")
        ->post("/acme/settings/integrations/google-sheets/{$connection->id}/verify")
        ->assertRedirect("/acme/settings/integrations/google-sheets/{$connection->id}")
        ->assertSessionHasErrors('verify');

    expect($connection->fresh()->status)->toBe(GoogleSheetConnectionStatus::Draft);
});

test('users without integrations manage cannot create a google sheet connection', function () {
    createTestTenant();
    tenancy()->initialize(Tenant::query()->findOrFail('acme'));

    $role = Role::query()->create([
        'name' => 'Viewer',
        'slug' => 'viewer',
        'description' => 'View integrations only',
        'is_system' => false,
    ]);

    $viewPermission = Permission::query()->where('key', TenantPermission::IntegrationsView->value)->firstOrFail();
    $role->permissions()->sync([$viewPermission->id]);

    $user = User::query()->create([
        'name' => 'Integration Viewer',
        'email' => 'viewer@acme.test',
        'password' => 'password',
        'email_verified_at' => now(),
        'role_id' => $role->id,
    ]);

    actingAsTenantUser($user);

    $this->post('/acme/settings/integrations/google-sheets', [
        'name' => 'Blocked',
        'spreadsheet_url' => 'https://docs.google.com/spreadsheets/d/1BlockedSheetXXXXXXXXXXXXXXXX/edit',
    ])->assertForbidden();
});

test('scheduled sync imports new rows for connected sheets', function () {
    createTestTenant();
    actingAsTenantUser();

    /** @var FakeGoogleSheetsClient $sheets */
    $sheets = app(GoogleSheetsClient::class);
    $spreadsheetId = '1ScheduledSyncSheetXXXXXXXXXXXXXX';
    $sheets->seed(
        $spreadsheetId,
        ['Name', 'Phone'],
        [
            ['Asha', '9000000001'],
            ['Vikram', '9000000002'],
        ],
    );

    $connection = GoogleSheetConnection::factory()->connected(
        ['Name', 'Phone'],
        ['name' => 'Name', 'phone' => 'Phone'],
    )->create([
        'created_by_id' => tenantUser()->id,
        'name' => 'Ads sheet',
        'spreadsheet_id' => $spreadsheetId,
        'spreadsheet_url' => "https://docs.google.com/spreadsheets/d/{$spreadsheetId}/edit",
        'last_synced_row' => 1,
    ]);

    $this->artisan('google-sheets:sync')->assertSuccessful();

    expect(Lead::query()->count())->toBe(2)
        ->and($connection->fresh()->last_synced_row)->toBe(3)
        ->and($connection->fresh()->total_synced)->toBe(2);

    $this->artisan('google-sheets:sync')->assertSuccessful();

    expect(Lead::query()->count())->toBe(2);
});

test('authorized users can sync all and pause an active sheet from the index', function () {
    createTestTenant();
    actingAsTenantUser();

    /** @var FakeGoogleSheetsClient $sheets */
    $sheets = app(GoogleSheetsClient::class);
    $spreadsheetId = '1ManualSyncSheetXXXXXXXXXXXXXXXX';
    $sheets->seed(
        $spreadsheetId,
        ['Name', 'Phone'],
        [['Neha', '9000000099']],
    );

    $connection = GoogleSheetConnection::factory()->connected(
        ['Name', 'Phone'],
        ['name' => 'Name', 'phone' => 'Phone'],
    )->create([
        'created_by_id' => tenantUser()->id,
        'name' => 'Manual sheet',
        'spreadsheet_id' => $spreadsheetId,
        'spreadsheet_url' => "https://docs.google.com/spreadsheets/d/{$spreadsheetId}/edit",
        'last_synced_row' => 1,
    ]);

    $this->from('/acme/settings/integrations/google-sheets')
        ->post('/acme/settings/integrations/google-sheets/sync-all')
        ->assertRedirect('/acme/settings/integrations/google-sheets')
        ->assertSessionHas('status');

    expect(Lead::query()->where('name', 'Neha')->exists())->toBeTrue()
        ->and($connection->fresh()->total_synced)->toBe(1);

    $this->from('/acme/settings/integrations/google-sheets')
        ->post("/acme/settings/integrations/google-sheets/{$connection->id}/pause")
        ->assertRedirect('/acme/settings/integrations/google-sheets')
        ->assertSessionHas('status');

    expect($connection->fresh()->status)->toBe(GoogleSheetConnectionStatus::Paused);
});
