<?php

use App\Enums\ProjectStatus;
use App\Enums\PropertyType;
use App\Models\Property;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

test('guests are redirected from properties page', function () {
    createTestTenant();

    $this->get('/acme/properties')
        ->assertRedirect(route('tenant.login', ['tenant' => 'acme']));
});

test('tenant users can view properties list', function () {
    createTestTenant();
    actingAsTenantUser();

    $this->get('/acme/properties')
        ->assertOk()
        ->assertSee('Properties')
        ->assertSee(route('tenant.properties.create', ['tenant' => 'acme'], false));
});

test('properties index shows card layout with details popup content', function () {
    createTestTenant();
    actingAsTenantUser();

    $property = Property::factory()->create([
        'project_name' => 'Card View Project',
        'developer_name' => 'Card Developer',
        'project_location' => 'Mumbai',
        'amenities' => ['Swimming Pool', 'Gym'],
        'configurations' => [
            [
                'name' => '2 BHK',
                'carpet_area_sqft' => 850,
                'price' => 9500000,
                'unit_count' => 120,
            ],
        ],
    ]);

    $this->get('/acme/properties')
        ->assertOk()
        ->assertSee('Card View Project')
        ->assertSee('Card Developer')
        ->assertSee('Mumbai')
        ->assertSee('property-details-'.$property->id, false)
        ->assertSee('Basic Info')
        ->assertSee('Project')
        ->assertSee('Tagging')
        ->assertSee('Amenities')
        ->assertSee('Configurations')
        ->assertSee('Attachments')
        ->assertSee('Swimming Pool')
        ->assertSee('2 BHK')
        ->assertDontSee('<table', false);
});

test('tenant users can view add property form page', function () {
    createTestTenant();
    actingAsTenantUser();

    $this->get('/acme/properties/create')
        ->assertOk()
        ->assertSee('Add Property')
        ->assertSee('Basic Information')
        ->assertSee('Project Scale')
        ->assertSee('Tagging & Payout')
        ->assertSee('Amenities')
        ->assertSee('Configurations (Unit Variants)')
        ->assertSee('Attachments')
        ->assertSee('Brochure Files')
        ->assertSee('Choose files')
        ->assertSee('Add Configuration')
        ->assertSee('Type an amenity and press Enter or click Add. Add as many as needed.')
        ->assertSee('Palava City Phase 3', false)
        ->assertSee('Create Property')
        ->assertSee('Back to Properties');
});

test('guests are redirected from add property page', function () {
    createTestTenant();

    $this->get('/acme/properties/create')
        ->assertRedirect(route('tenant.login', ['tenant' => 'acme']));
});

test('tenant users can create a property', function () {
    createTestTenant();
    actingAsTenantUser();

    $this->from('/acme/properties/create')
        ->post('/acme/properties', [
            'developer_name' => 'Skyline Developers',
            'project_name' => 'Sunset Villa',
            'project_location' => 'Goa',
            'rera_number' => 'RERA12345',
            'property_type' => PropertyType::Villa->value,
            'project_status' => ProjectStatus::NewLaunch->value,
            'possession_date' => '08-2026',
        ])->assertRedirect(route('tenant.properties.index', ['tenant' => 'acme']))
        ->assertSessionHas('status');

    $property = Property::query()->where('project_name', 'Sunset Villa')->first();

    expect($property)->not->toBeNull()
        ->and($property->developer_name)->toBe('Skyline Developers')
        ->and($property->project_location)->toBe('Goa')
        ->and($property->rera_number)->toBe('RERA12345')
        ->and($property->property_type)->toBe(PropertyType::Villa)
        ->and($property->project_status)->toBe(ProjectStatus::NewLaunch)
        ->and($property->possession_date)->toBe('08-2026');
});

test('tenant users can create a property with project scale fields', function () {
    createTestTenant();
    actingAsTenantUser();

    $this->from('/acme/properties/create')
        ->post('/acme/properties', [
            'project_name' => 'Scale Project',
            'total_land_parcel_acres' => 12.5,
            'total_towers' => 3,
            'total_floors' => 'G+B+22',
            'carpet_area_from_sqft' => 850,
            'carpet_area_to_sqft' => 1450,
            'price_from' => 5500000,
            'price_to' => 12000000,
        ])->assertRedirect(route('tenant.properties.index', ['tenant' => 'acme']))
        ->assertSessionHas('status');

    $property = Property::query()->where('project_name', 'Scale Project')->first();

    expect($property)->not->toBeNull()
        ->and((float) $property->total_land_parcel_acres)->toBe(12.5)
        ->and($property->total_towers)->toBe(3)
        ->and($property->total_floors)->toBe('G+B+22')
        ->and($property->carpet_area_from_sqft)->toBe(850)
        ->and($property->carpet_area_to_sqft)->toBe(1450)
        ->and($property->price_from)->toBe(5500000)
        ->and($property->price_to)->toBe(12000000);
});

test('tenant users can create a property with tagging and payout fields', function () {
    createTestTenant();
    actingAsTenantUser();

    $this->from('/acme/properties/create')
        ->post('/acme/properties', [
            'project_name' => 'Tagging Project',
            'tagging_period_days' => 90,
            'payout_percent' => 2.5,
            'sourcing_manager_name' => 'Rajesh Kumar',
            'sourcing_manager_contact' => '+91 98765 43210',
        ])->assertRedirect(route('tenant.properties.index', ['tenant' => 'acme']))
        ->assertSessionHas('status');

    $property = Property::query()->where('project_name', 'Tagging Project')->first();

    expect($property)->not->toBeNull()
        ->and($property->tagging_period_days)->toBe(90)
        ->and((float) $property->payout_percent)->toBe(2.5)
        ->and($property->sourcing_manager_name)->toBe('Rajesh Kumar')
        ->and($property->sourcing_manager_contact)->toBe('+91 98765 43210');
});

test('create property validates payout percent range', function () {
    createTestTenant();
    actingAsTenantUser();

    $this->from('/acme/properties/create')
        ->post('/acme/properties', [
            'project_name' => 'Invalid Payout',
            'payout_percent' => 150,
        ])
        ->assertSessionHasErrors('payout_percent');
});

test('tenant users can create a property with amenities', function () {
    createTestTenant();
    actingAsTenantUser();

    $this->from('/acme/properties/create')
        ->post('/acme/properties', [
            'project_name' => 'Amenities Project',
            'amenities' => ['Swimming Pool', 'Gym', 'Clubhouse'],
        ])->assertRedirect(route('tenant.properties.index', ['tenant' => 'acme']))
        ->assertSessionHas('status');

    $property = Property::query()->where('project_name', 'Amenities Project')->first();

    expect($property)->not->toBeNull()
        ->and($property->amenities)->toBe(['Swimming Pool', 'Gym', 'Clubhouse']);
});

test('create property validates amenity name length', function () {
    createTestTenant();
    actingAsTenantUser();

    $this->from('/acme/properties/create')
        ->post('/acme/properties', [
            'project_name' => 'Invalid Amenity',
            'amenities' => [str_repeat('a', 101)],
        ])
        ->assertSessionHasErrors('amenities.0');
});

test('tenant users can create a property with configurations', function () {
    createTestTenant();
    actingAsTenantUser();

    $this->from('/acme/properties/create')
        ->post('/acme/properties', [
            'project_name' => 'Configurations Project',
            'configurations' => [
                [
                    'name' => '2 BHK',
                    'carpet_area_sqft' => 850,
                    'price' => 9500000,
                    'unit_count' => 120,
                ],
                [
                    'name' => '3 BHK + Study',
                    'carpet_area_sqft' => 1150,
                    'price' => 13500000,
                    'unit_count' => 60,
                ],
            ],
        ])->assertRedirect(route('tenant.properties.index', ['tenant' => 'acme']))
        ->assertSessionHas('status');

    $property = Property::query()->where('project_name', 'Configurations Project')->first();

    expect($property)->not->toBeNull()
        ->and($property->configurations)->toBe([
            [
                'name' => '2 BHK',
                'carpet_area_sqft' => 850,
                'price' => 9500000,
                'unit_count' => 120,
            ],
            [
                'name' => '3 BHK + Study',
                'carpet_area_sqft' => 1150,
                'price' => 13500000,
                'unit_count' => 60,
            ],
        ]);
});

test('create property validates configuration name is required', function () {
    createTestTenant();
    actingAsTenantUser();

    $this->from('/acme/properties/create')
        ->post('/acme/properties', [
            'project_name' => 'Invalid Configuration',
            'configurations' => [
                [
                    'name' => '',
                    'carpet_area_sqft' => 850,
                    'price' => 9500000,
                    'unit_count' => 10,
                ],
            ],
        ])
        ->assertSessionHasErrors('configurations.0.name');
});

test('tenant users can create a property with attachments', function () {
    createTestTenant();
    actingAsTenantUser();

    Storage::fake('local');

    $layout = UploadedFile::fake()->create('tower-a-layout.pdf', 100, 'application/pdf');
    $brochure = UploadedFile::fake()->create('project-brochure.pdf', 100, 'application/pdf');

    $this->from('/acme/properties/create')
        ->post('/acme/properties', [
            'project_name' => 'Attachments Project',
            'layout_files' => [$layout],
            'brochure_files' => [$brochure],
        ])->assertRedirect(route('tenant.properties.index', ['tenant' => 'acme']))
        ->assertSessionHas('status');

    $property = Property::query()->where('project_name', 'Attachments Project')->first();

    expect($property)->not->toBeNull()
        ->and($property->layout_files)->toHaveCount(1)
        ->and($property->layout_files[0]['name'])->toBe('tower-a-layout.pdf')
        ->and($property->brochure_files)->toHaveCount(1)
        ->and($property->brochure_files[0]['name'])->toBe('project-brochure.pdf');

    Storage::disk('local')->assertExists($property->layout_files[0]['path']);
    Storage::disk('local')->assertExists($property->brochure_files[0]['path']);
});

test('create property validates attachment file types', function () {
    createTestTenant();
    actingAsTenantUser();

    Storage::fake('local');

    $invalidFile = UploadedFile::fake()->create('notes.txt', 10, 'text/plain');

    $this->from('/acme/properties/create')
        ->post('/acme/properties', [
            'project_name' => 'Invalid Attachment',
            'layout_files' => [$invalidFile],
        ])
        ->assertSessionHasErrors('layout_files.0');
});

test('create property validates project scale ranges', function () {
    createTestTenant();
    actingAsTenantUser();

    $this->from('/acme/properties/create')
        ->post('/acme/properties', [
            'project_name' => 'Invalid Ranges',
            'carpet_area_from_sqft' => 1500,
            'carpet_area_to_sqft' => 900,
            'price_from' => 9000000,
            'price_to' => 5000000,
        ])
        ->assertSessionHasErrors(['carpet_area_to_sqft', 'price_to']);
});

test('properties index edit modal includes full form prefilled with saved property data', function () {
    createTestTenant();
    actingAsTenantUser();

    $property = Property::factory()->create([
        'project_name' => 'Editable Towers',
        'developer_name' => 'Edit Developer',
        'project_location' => 'Pune',
        'rera_number' => 'RERA98765',
        'property_type' => PropertyType::Apartment,
        'project_status' => ProjectStatus::UnderConstruction,
        'possession_date' => '12-2027',
        'total_land_parcel_acres' => 12.5,
        'total_towers' => 4,
        'total_floors' => 'G+20',
        'carpet_area_from_sqft' => 700,
        'carpet_area_to_sqft' => 1400,
        'price_from' => 6000000,
        'price_to' => 12000000,
        'tagging_period_days' => 90,
        'payout_percent' => 2.5,
        'sourcing_manager_name' => 'Ravi Kumar',
        'sourcing_manager_contact' => '+91 9876543210',
        'amenities' => ['Gym', 'Pool'],
        'configurations' => [
            [
                'name' => '3 BHK Premium',
                'carpet_area_sqft' => 1250,
                'price' => 11500000,
                'unit_count' => 45,
            ],
        ],
    ]);

    $this->get('/acme/properties')
        ->assertOk()
        ->assertSee('edit-property-'.$property->id, false)
        ->assertSee('Editable Towers')
        ->assertSee('Edit Developer')
        ->assertSee('Pune')
        ->assertSee('RERA98765')
        ->assertSee('Project Scale')
        ->assertSee('Tagging & Payout')
        ->assertSee('Configurations (Unit Variants)')
        ->assertSee('3 BHK Premium')
        ->assertSee('Gym')
        ->assertSee('Ravi Kumar')
        ->assertSee('Save Changes');
});

test('tenant users can update a property', function () {
    createTestTenant();
    actingAsTenantUser();

    $property = Property::factory()->create([
        'project_name' => 'Before Edit',
        'project_status' => ProjectStatus::NewLaunch,
    ]);

    $this->from('/acme/properties')
        ->patch('/acme/properties/'.$property->id, [
            'developer_name' => $property->developer_name,
            'project_name' => 'After Edit',
            'project_location' => $property->project_location,
            'rera_number' => $property->rera_number,
            'property_type' => $property->property_type?->value,
            'project_status' => ProjectStatus::ReadyToMove->value,
            'possession_date' => $property->possession_date,
        ])
        ->assertRedirect(route('tenant.properties.index', ['tenant' => 'acme']))
        ->assertSessionHas('status');

    $property->refresh();

    expect($property->project_name)->toBe('After Edit')
        ->and($property->project_status)->toBe(ProjectStatus::ReadyToMove);
});

test('tenant users can delete a property', function () {
    createTestTenant();
    actingAsTenantUser();

    $property = Property::factory()->create(['project_name' => 'To Delete']);

    $this->from('/acme/properties')
        ->delete('/acme/properties/'.$property->id)
        ->assertRedirect(route('tenant.properties.index', ['tenant' => 'acme']))
        ->assertSessionHas('status');

    expect(Property::query()->find($property->id))->toBeNull()
        ->and(Property::withTrashed()->find($property->id))->not->toBeNull();
});

test('tenant users can search properties by project name', function () {
    createTestTenant();
    actingAsTenantUser();

    Property::factory()->create(['project_name' => 'Ocean View']);
    Property::factory()->create(['project_name' => 'Hill Top']);

    $this->get('/acme/properties?search=Ocean')
        ->assertOk()
        ->assertSee('Ocean View')
        ->assertDontSee('Hill Top');
});

test('create property validates possession date format', function () {
    createTestTenant();
    actingAsTenantUser();

    $this->from('/acme/properties/create')
        ->post('/acme/properties', [
            'project_name' => 'Invalid Date Project',
            'possession_date' => '2026-08',
        ])
        ->assertSessionHasErrors('possession_date');
});

test('legacy property type labels are normalized when saving', function () {
    createTestTenant();
    actingAsTenantUser();

    $this->from('/acme/properties/create')
        ->post('/acme/properties', [
            'project_name' => 'Legacy Label Project',
            'property_type' => 'Apartment',
        ])->assertRedirect(route('tenant.properties.index', ['tenant' => 'acme']));

    $property = Property::query()->where('project_name', 'Legacy Label Project')->first();

    expect($property)->not->toBeNull()
        ->and($property->property_type)->toBe(PropertyType::Apartment);
});

test('properties with legacy property type values can be loaded', function () {
    createTestTenant();
    actingAsTenantUser();

    DB::table('properties')->insert([
        'developer_name' => 'Legacy Dev',
        'project_name' => 'Legacy Project',
        'property_type' => 'Apartment',
        'created_by_id' => tenantUser()->id,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->get('/acme/properties')
        ->assertOk()
        ->assertSee('Legacy Project');
});
