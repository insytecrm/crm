<?php

use App\Models\Property;
use App\Models\Tenant;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('guests are redirected from the microsite cms', function () {
    createTestTenant();
    tenancy()->initialize(Tenant::query()->findOrFail('acme'));

    $property = Property::factory()->withMicrosite('cms-project')->create();

    $this->get('/acme/properties/'.$property->id.'/microsite/manage')
        ->assertRedirect(route('tenant.login', ['tenant' => 'acme']));
});

test('cms is not found when the microsite is disabled', function () {
    createTestTenant();
    actingAsTenantUser();

    $property = Property::factory()->create([
        'microsite_enabled' => false,
        'microsite_slug' => 'off-project',
    ]);

    $this->get('/acme/properties/'.$property->id.'/microsite/manage')->assertNotFound();
});

test('tenant users can open and save the microsite cms', function () {
    createTestTenant();
    actingAsTenantUser();

    $property = Property::factory()->withMicrosite('editable-project')->create([
        'project_name' => 'Editable Project',
    ]);

    $this->withoutVite();

    $this->get('/acme/properties/'.$property->id.'/microsite/manage')
        ->assertOk()
        ->assertSee('Manage microsite')
        ->assertSee('Editable Project')
        ->assertSee('Lead form')
        ->assertSee('Primary font')
        ->assertSee('Leave blank to keep the template default', false);

    $this->from('/acme/properties/'.$property->id.'/microsite/manage')
        ->patch('/acme/properties/'.$property->id.'/microsite/content', [
            'theme_preset' => 'ivory',
            'theme_accent' => '#8a6a3b',
            'font_primary' => 'inter',
            'font_secondary' => 'playfair_display',
            'phone' => '+91 9000000000',
            'whatsapp' => '919000000000',
            'cta_label' => 'Get Project Details',
            'lead_form' => [
                'title' => 'Request a callback',
                'subtitle' => 'We will share pricing shortly.',
                'submit_label' => 'Send enquiry',
                'success_message' => 'Thanks — we will call you soon.',
                'show_name' => '1',
                'show_phone' => '1',
                'show_email' => '1',
                'show_configuration' => '0',
                'require_name' => '1',
                'require_phone' => '1',
                'require_email' => '0',
                'require_configuration' => '0',
                'label_name' => 'Full name',
            ],
            'sections' => [
                'hero' => [
                    'headline' => 'Replaced Hero Headline',
                    'subheadline' => 'Replaced subhead',
                    'button_label' => 'Check availability',
                ],
                'about' => [
                    'body' => 'A concise editorial about the project.',
                ],
                'location' => [
                    'map_url' => 'https://www.google.com/maps?q=Pune',
                ],
                'cta' => [
                    'button_label' => 'Talk to sales',
                ],
            ],
        ])
        ->assertRedirect()
        ->assertSessionHas('status', 'Microsite content saved.');

    $property->refresh();

    expect($property->microsite)->not->toBeNull()
        ->and($property->microsite->theme()->value)->toBe('ivory')
        ->and($property->microsite->primaryFont()->value)->toBe('inter')
        ->and($property->microsite->secondaryFont()->value)->toBe('playfair_display')
        ->and($property->microsite->phone)->toBe('+91 9000000000')
        ->and($property->microsite->sectionContent()['hero']['headline'])->toBe('Replaced Hero Headline')
        ->and($property->microsite->sectionContent()['hero']['button_label'])->toBe('Check availability')
        ->and($property->microsite->leadFormSettings()['title'])->toBe('Request a callback')
        ->and($property->microsite->leadFormSettings()['show_configuration'])->toBeFalse();

    $this->withoutVite();

    $this->get('/acme/projects/editable-project')
        ->assertOk()
        ->assertSee('Replaced Hero Headline')
        ->assertSee('Check availability')
        ->assertSee('Talk to sales')
        ->assertSee('Request a callback')
        ->assertSee('Full name')
        ->assertSee('Get Project Details')
        ->assertSee('Call');
});

test('cms can replace a hero image', function () {
    createTestTenant();
    actingAsTenantUser();
    Storage::fake('local');

    $property = Property::factory()->withMicrosite('media-project')->create();

    $this->patch('/acme/properties/'.$property->id.'/microsite/content', [
        'theme_preset' => 'noir',
        'font_primary' => 'outfit',
        'font_secondary' => 'cormorant_garamond',
        'hero_image' => UploadedFile::fake()->image('hero.jpg'),
    ])->assertRedirect();

    $property->refresh();
    $hero = $property->microsite?->mediaLibrary()['hero'];

    expect($hero)->not->toBeNull();
    Storage::disk('local')->assertExists($hero['path']);

    $this->get('/acme/projects/media-project/media/hero')->assertOk();
});
