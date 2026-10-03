<?php

use App\Enums\ProjectStatus;
use App\Enums\PropertyType;
use App\Models\Property;
use App\Support\PropertyCsvSchema;
use Illuminate\Http\UploadedFile;

test('properties index shows import and export actions', function () {
    createTestTenant();
    actingAsTenantUser();

    $this->get('/acme/properties')
        ->assertOk()
        ->assertSee('Import')
        ->assertSee('Export')
        ->assertSee('import-properties', false);
});

test('users can download the property import sample csv', function () {
    createTestTenant();
    actingAsTenantUser();

    $this->get('/acme/properties-import/sample')
        ->assertOk()
        ->assertHeader('content-type', 'text/csv; charset=UTF-8')
        ->assertDownload('properties-import-sample.csv');
});

test('users can import properties from csv', function () {
    createTestTenant();
    actingAsTenantUser();

    $headers = PropertyCsvSchema::headers();
    $row = [
        'Imported Towers',
        'Imported Developers',
        'Pune',
        'P51800009999',
        PropertyType::Apartment->label(),
        ProjectStatus::ReadyToMove->label(),
        '06-2028',
        '2.5',
        '2',
        'G+15',
        '700',
        '1200',
        '5000000',
        '9000000',
        '60',
        '2.5',
        'Amit Patel',
        '+91 9123456789',
        'Gym;Parking',
        '2 BHK|850|7500000|100',
    ];

    $csv = csvFixture($headers, [$row]);
    $file = UploadedFile::fake()->createWithContent('properties.csv', $csv);

    $this->post('/acme/properties-import', ['file' => $file])
        ->assertRedirect(route('tenant.properties.index', ['tenant' => 'acme']))
        ->assertSessionHas('status');

    $property = Property::query()->where('project_name', 'Imported Towers')->first();

    expect($property)->not->toBeNull()
        ->and($property->developer_name)->toBe('Imported Developers')
        ->and($property->property_type)->toBe(PropertyType::Apartment)
        ->and($property->project_status)->toBe(ProjectStatus::ReadyToMove)
        ->and($property->amenities)->toBe(['Gym', 'Parking'])
        ->and($property->configurations)->toHaveCount(1)
        ->and($property->configurations[0]['name'])->toBe('2 BHK');
});

test('property import skips blank project names', function () {
    createTestTenant();
    actingAsTenantUser();

    $headers = PropertyCsvSchema::headers();
    $validRow = PropertyCsvSchema::sampleRow();
    $validRow[0] = 'Valid Import Project';
    $blankRow = array_fill(0, count($headers), '');
    $csv = csvFixture($headers, [$blankRow, $validRow]);
    $file = UploadedFile::fake()->createWithContent('properties.csv', $csv);

    $this->post('/acme/properties-import', ['file' => $file])
        ->assertRedirect(route('tenant.properties.index', ['tenant' => 'acme']))
        ->assertSessionHas('status');

    expect(Property::query()->where('project_name', 'Valid Import Project')->exists())->toBeTrue()
        ->and(Property::query()->count())->toBe(1);
});

test('users can export properties as csv', function () {
    createTestTenant();
    actingAsTenantUser();

    Property::factory()->create([
        'project_name' => 'Export Me',
        'developer_name' => 'Export Developer',
    ]);

    $response = $this->get('/acme/properties-export');

    $response->assertOk()
        ->assertHeader('content-type', 'text/csv; charset=UTF-8')
        ->assertDownload('properties-'.now()->format('Y-m-d').'.csv');

    expect($response->streamedContent())->toContain('Export Me')
        ->and($response->streamedContent())->toContain('Export Developer');
});
