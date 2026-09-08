<?php

use App\Enums\LeadSource;
use App\Models\Lead;
use App\Models\Property;
use App\Models\Tenant;

test('guests can view an enabled property microsite', function () {
    createTestTenant();
    tenancy()->initialize(Tenant::query()->findOrFail('acme'));

    $property = Property::factory()->withMicrosite('the-reserve')->create([
        'project_name' => 'The Reserve',
    ]);

    $micrositeUrl = $property->micrositeUrl();

    $this->withoutVite();

    $this->get('/acme/projects/the-reserve')
        ->assertOk()
        ->assertSee('The Reserve')
        ->assertSee('Enquire Now')
        ->assertDontSee('This microsite is live');

    expect($micrositeUrl)->toEndWith('/acme/projects/the-reserve');
});

test('disabled microsites return not found', function () {
    createTestTenant();
    tenancy()->initialize(Tenant::query()->findOrFail('acme'));

    Property::factory()->create([
        'project_name' => 'Hidden Project',
        'microsite_enabled' => false,
        'microsite_slug' => 'hidden-project',
    ]);

    $this->get('/acme/projects/hidden-project')->assertNotFound();
});

test('tenant users can enable a microsite and get a slug plus public link', function () {
    createTestTenant();
    actingAsTenantUser();

    $property = Property::factory()->create([
        'project_name' => 'The Reserve',
        'microsite_enabled' => false,
        'microsite_slug' => null,
    ]);

    $this->from('/acme/properties')
        ->patch('/acme/properties/'.$property->id.'/microsite', [
            'microsite_enabled' => '1',
        ])
        ->assertRedirect('/acme/properties')
        ->assertSessionHas('status', 'Microsite enabled.');

    $property->refresh();

    expect($property->microsite_enabled)->toBeTrue()
        ->and($property->microsite_slug)->toBe('the-reserve');

    $this->get('/acme/properties')
        ->assertOk()
        ->assertSee('the-reserve', false);

    $this->withoutVite();

    $this->get('/acme/projects/the-reserve')
        ->assertOk()
        ->assertSee('The Reserve');
});

test('microsite toggle can be updated via ajax without a redirect', function () {
    createTestTenant();
    actingAsTenantUser();

    $property = Property::factory()->create([
        'project_name' => 'Ajax Reserve',
        'microsite_enabled' => false,
        'microsite_slug' => null,
    ]);

    $this->patchJson('/acme/properties/'.$property->id.'/microsite', [
        'microsite_enabled' => '1',
    ])
        ->assertOk()
        ->assertJsonPath('microsite_enabled', true)
        ->assertJsonPath('message', 'Microsite enabled.')
        ->assertJsonPath('microsite_url', fn ($url) => str_ends_with((string) $url, '/acme/projects/ajax-reserve'));

    $property->refresh();

    expect($property->hasMicrosite())->toBeTrue()
        ->and($property->microsite_slug)->toBe('ajax-reserve');

    $this->patchJson('/acme/properties/'.$property->id.'/microsite', [
        'microsite_enabled' => '0',
    ])
        ->assertOk()
        ->assertJsonPath('microsite_enabled', false)
        ->assertJsonPath('microsite_url', null);
});

test('tenant users can disable a microsite while keeping the slug', function () {
    createTestTenant();
    actingAsTenantUser();

    $property = Property::factory()->withMicrosite('keep-slug')->create([
        'project_name' => 'Keep Slug Project',
    ]);

    $this->from('/acme/properties')
        ->patch('/acme/properties/'.$property->id.'/microsite', [
            'microsite_enabled' => '0',
        ])
        ->assertRedirect('/acme/properties')
        ->assertSessionHas('status', 'Microsite disabled.');

    $property->refresh();

    expect($property->microsite_enabled)->toBeFalse()
        ->and($property->microsite_slug)->toBe('keep-slug');

    $this->get('/acme/projects/keep-slug')->assertNotFound();
});

test('enabling a microsite generates a unique slug when the base is taken', function () {
    createTestTenant();
    actingAsTenantUser();

    Property::factory()->withMicrosite('shared-name')->create([
        'project_name' => 'Shared Name',
    ]);

    $property = Property::factory()->create([
        'project_name' => 'Shared Name',
        'microsite_enabled' => false,
        'microsite_slug' => null,
    ]);

    $this->patch('/acme/properties/'.$property->id.'/microsite', [
        'microsite_enabled' => '1',
    ])->assertRedirect();

    expect($property->fresh()->microsite_slug)->toBe('shared-name-2');
});

test('show on website does not control microsite availability', function () {
    createTestTenant();
    tenancy()->initialize(Tenant::query()->findOrFail('acme'));

    Property::factory()->withMicrosite('solo-page')->create([
        'project_name' => 'Solo Page',
        'show_on_website' => false,
    ]);

    $this->withoutVite();

    $this->get('/acme/projects/solo-page')
        ->assertOk()
        ->assertSee('Solo Page');
});

test('creating a property can enable microsite from the form', function () {
    createTestTenant();
    actingAsTenantUser();

    $this->post('/acme/properties', [
        'project_name' => 'New Microsite Project',
        'property_type' => 'apartment',
        'microsite_enabled' => '1',
    ])->assertRedirect(route('tenant.properties.index', ['tenant' => 'acme']));

    $property = Property::query()->where('project_name', 'New Microsite Project')->first();

    expect($property)->not->toBeNull()
        ->and($property->microsite_enabled)->toBeTrue()
        ->and($property->microsite_slug)->toBe('new-microsite-project');
});

test('public microsite hides internal crm fields and groups configurations', function () {
    createTestTenant();
    tenancy()->initialize(Tenant::query()->findOrFail('acme'));

    Property::factory()->withMicrosite('grouped-project')->create([
        'project_name' => 'Grouped Project',
        'sourcing_manager_name' => 'SECRET_MANAGER_XYZ',
        'sourcing_manager_contact' => 'SECRET_CONTACT_999',
        'payout_percent' => 9.99,
        'tagging_period_days' => 77,
        'configurations' => [
            ['name' => '2 BHK', 'carpet_area_sqft' => 700, 'price' => 8000000, 'unit_count' => 10],
            ['name' => '2 BHK', 'carpet_area_sqft' => 850, 'price' => 9500000, 'unit_count' => 8],
            ['name' => '2 BHK', 'carpet_area_sqft' => 900, 'price' => 11000000, 'unit_count' => 4],
            ['name' => '3 BHK', 'carpet_area_sqft' => 1200, 'price' => 15000000, 'unit_count' => 6],
        ],
    ]);

    $this->withoutVite();

    $this->get('/acme/projects/grouped-project')
        ->assertOk()
        ->assertSee('Grouped Project')
        ->assertSee('2 BHK')
        ->assertSee('3 BHK')
        ->assertSee('3 variants')
        ->assertDontSee('SECRET_MANAGER_XYZ')
        ->assertDontSee('SECRET_CONTACT_999');
});

test('replaced cms headline is shown on the public microsite', function () {
    createTestTenant();
    tenancy()->initialize(Tenant::query()->findOrFail('acme'));

    $property = Property::factory()->withMicrosite('headline-project')->create([
        'project_name' => 'Headline Project',
    ]);

    $property->microsite()->create([
        'theme_preset' => 'noir',
        'content' => [
            'hero' => [
                'headline' => 'Custom Skyline Headline',
                'subheadline' => null,
                'body' => null,
                'video_url' => null,
                'map_url' => null,
            ],
        ],
        'media' => [],
    ]);

    $this->withoutVite();

    $this->get('/acme/projects/headline-project')
        ->assertOk()
        ->assertSee('Custom Skyline Headline');
});

test('public microsite escapes cms copy', function () {
    createTestTenant();
    tenancy()->initialize(Tenant::query()->findOrFail('acme'));

    $property = Property::factory()->withMicrosite('escape-project')->create([
        'project_name' => 'Escape Project',
    ]);

    $property->microsite()->create([
        'theme_preset' => 'noir',
        'content' => [
            'hero' => [
                'headline' => '<script>alert(1)</script>',
                'subheadline' => null,
                'body' => null,
                'video_url' => null,
                'map_url' => null,
            ],
        ],
        'media' => [],
    ]);

    $this->withoutVite();

    $this->get('/acme/projects/escape-project')
        ->assertOk()
        ->assertDontSee('<script>alert(1)</script>', false)
        ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false);
});

test('guests can submit a microsite enquiry', function () {
    createTestTenant();
    tenancy()->initialize(Tenant::query()->findOrFail('acme'));

    Property::factory()->withMicrosite('enquire-project')->create([
        'project_name' => 'Enquire Project',
        'project_location' => 'Pune',
    ]);

    $this->from('/acme/projects/enquire-project')
        ->post('/acme/projects/enquire-project/enquire', [
            'name' => 'Riya Shah',
            'phone' => '9876543210',
            'email' => 'riya@example.com',
        ])
        ->assertRedirect('/acme/projects/enquire-project')
        ->assertSessionHas('status');

    tenancy()->initialize(Tenant::query()->findOrFail('acme'));

    $lead = Lead::query()->where('phone', '9876543210')->first();

    expect($lead)->not->toBeNull()
        ->and($lead->name)->toBe('Riya Shah')
        ->and($lead->source)->toBe(LeadSource::Microsite->value)
        ->and($lead->sub_source)->toBe('Enquire Project')
        ->and($lead->location)->toBe('Pune');
});

test('disabled microsite media returns not found', function () {
    createTestTenant();
    tenancy()->initialize(Tenant::query()->findOrFail('acme'));

    Property::factory()->create([
        'project_name' => 'Hidden Media',
        'microsite_enabled' => false,
        'microsite_slug' => 'hidden-media',
    ]);

    $this->get('/acme/projects/hidden-media/media/hero')->assertNotFound();
});
