<?php

use App\Enums\LeadActivityType;
use App\Models\Booking;
use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\MessageTemplate;
use App\Models\Property;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;

test('guests cannot preview or send a lead whatsapp message', function () {
    createTestTenant();
    tenancy()->initialize(Tenant::query()->findOrFail('acme'));
    $lead = Lead::factory()->create(['phone' => '+91 9000000000']);

    $this->getJson('/acme/leads/'.$lead->id.'/whatsapp')
        ->assertUnauthorized();

    $this->post('/acme/leads/'.$lead->id.'/whatsapp', [
        'template_id' => 1,
    ])->assertRedirect(route('tenant.login', ['tenant' => 'acme']));
});

test('lead list whatsapp button opens the template popup', function () {
    createTestTenant();
    actingAsTenantUser();
    Lead::factory()->create(['phone' => '+91 9000000000']);

    $this->get('/acme/leads')
        ->assertOk()
        ->assertSee('send-whatsapp', false)
        ->assertSee('prepare-whatsapp', false)
        ->assertSee('Select template')
        ->assertSee('Custom message')
        ->assertSee('Message')
        ->assertSee('Send message')
        ->assertSee('name="message"', false)
        ->assertDontSee('window.open', false)
        ->assertDontSee('name="type" value="whatsapp_message"', false);
});

test('whatsapp preview replaces variables with the lead real details', function () {
    createTestTenant();
    $user = actingAsTenantUser();

    $lead = Lead::factory()->create([
        'name' => 'Rahul Sharma',
        'phone' => '+91 9000000000',
        'assigned_to_id' => $user->id,
    ]);
    $property = Property::factory()->create([
        'project_name' => 'Riverfront Residences',
        'created_by_id' => $user->id,
    ]);
    Booking::factory()->create([
        'lead_id' => $lead->id,
        'property_id' => $property->id,
        'configuration_name' => '3 BHK',
        'unit_number' => 'A-1204',
        'agreement_value' => 11_500_000,
        'booking_date' => '2026-09-06',
        'created_by_id' => $user->id,
    ]);
    MessageTemplate::factory()->create([
        'created_by_id' => $user->id,
        'name' => 'Site visit reminder',
        'body' => 'Hi {{lead.name}}, see {{property.project_name}} unit {{booking.unit_number}} from {{user.name}} at {{company.name}}.',
    ]);
    MessageTemplate::factory()->inactive()->create([
        'created_by_id' => $user->id,
        'name' => 'Hidden draft',
        'body' => 'Draft {{lead.name}}',
    ]);

    $preview = 'Hi Rahul Sharma, see Riverfront Residences unit A-1204 from Acme Admin at Acme Inc.';

    $this->getJson('/acme/leads/'.$lead->id.'/whatsapp')
        ->assertOk()
        ->assertJsonPath('lead_name', 'Rahul Sharma')
        ->assertJsonPath('has_phone', true)
        ->assertJsonPath('whatsapp_digits', '919000000000')
        ->assertJsonPath('templates.0.name', 'Site visit reminder')
        ->assertJsonPath('templates.0.preview', $preview)
        ->assertJsonMissing(['name' => 'Hidden draft']);
});

test('sending a whatsapp template logs the message and opens whatsapp web', function () {
    createTestTenant();
    $user = actingAsTenantUser();

    $lead = Lead::factory()->create([
        'name' => 'Rahul Sharma',
        'phone' => '+91 9000000000',
        'assigned_to_id' => $user->id,
    ]);
    $template = MessageTemplate::factory()->create([
        'created_by_id' => $user->id,
        'name' => 'Welcome',
        'body' => 'Hi {{lead.name}}, this is {{user.name}} from {{company.name}}.',
    ]);

    $message = 'Hi Rahul Sharma, this is Acme Admin from Acme Inc.';

    $this->from('/acme/leads?lead='.$lead->id)
        ->post('/acme/leads/'.$lead->id.'/whatsapp', [
            'template_id' => $template->id,
            'message' => $message,
        ])
        ->assertRedirect(route('tenant.whatsapp-web.index', [
            'tenant' => 'acme',
            'phone' => '919000000000',
            'message' => $message,
        ]))
        ->assertSessionHas('status', 'WhatsApp opened with your message.');

    $this->assertDatabaseHas('lead_activities', [
        'lead_id' => $lead->id,
        'type' => LeadActivityType::WhatsAppMessage->value,
        'description' => $message,
    ]);

    expect(LeadActivity::query()->where('lead_id', $lead->id)->where('type', LeadActivityType::WhatsAppMessage)->first()?->metadata)
        ->toMatchArray([
            'template_id' => $template->id,
            'template_name' => 'Welcome',
        ]);
});

test('whatsapp send rejects templates that are not active whatsapp templates', function (string $state) {
    createTestTenant();
    $user = actingAsTenantUser();

    $lead = Lead::factory()->create(['phone' => '+91 9000000000']);
    $factory = MessageTemplate::factory()->state([
        'created_by_id' => $user->id,
        'name' => 'Not for WhatsApp',
        'body' => 'Hi {{lead.name}}',
    ]);
    $template = match ($state) {
        'email' => $factory->email()->create(),
        'sms' => $factory->sms()->create(),
        'inactive' => $factory->inactive()->create(),
    };

    $this->from('/acme/leads')
        ->post('/acme/leads/'.$lead->id.'/whatsapp', [
            'template_id' => $template->id,
            'message' => 'Hi there',
        ])
        ->assertRedirect('/acme/leads')
        ->assertSessionHasErrors(['template_id' => 'Please choose a WhatsApp template.']);

    $this->assertDatabaseMissing('lead_activities', [
        'lead_id' => $lead->id,
        'type' => LeadActivityType::WhatsAppMessage->value,
    ]);
})->with(['email', 'sms', 'inactive']);

test('whatsapp send requires a message', function () {
    createTestTenant();
    actingAsTenantUser();

    $lead = Lead::factory()->create(['phone' => '+91 9000000000']);

    $this->from('/acme/leads')
        ->post('/acme/leads/'.$lead->id.'/whatsapp', [])
        ->assertRedirect('/acme/leads')
        ->assertSessionHasErrors(['message' => 'Please write a message to send.']);
});

test('a custom whatsapp message can be sent without a template', function () {
    createTestTenant();
    actingAsTenantUser();

    $lead = Lead::factory()->create([
        'name' => 'Rahul Sharma',
        'phone' => '+91 9000000000',
    ]);
    $message = 'Hi Rahul, are you free for a site visit tomorrow?';

    $this->from('/acme/leads?lead='.$lead->id)
        ->post('/acme/leads/'.$lead->id.'/whatsapp', [
            'message' => $message,
        ])
        ->assertRedirect(route('tenant.whatsapp-web.index', [
            'tenant' => 'acme',
            'phone' => '919000000000',
            'message' => $message,
        ]))
        ->assertSessionHas('status', 'WhatsApp opened with your message.');

    $this->assertDatabaseHas('lead_activities', [
        'lead_id' => $lead->id,
        'type' => LeadActivityType::WhatsAppMessage->value,
        'description' => $message,
    ]);

    expect(LeadActivity::query()->where('lead_id', $lead->id)->where('type', LeadActivityType::WhatsAppMessage)->first()?->metadata)
        ->toBeNull();
});

test('whatsapp send is rejected when the lead has no phone number', function () {
    createTestTenant();
    actingAsTenantUser();

    $lead = Lead::factory()->create(['phone' => null]);

    $this->from('/acme/leads')
        ->post('/acme/leads/'.$lead->id.'/whatsapp', [
            'message' => 'Hi there',
        ])
        ->assertRedirect('/acme/leads')
        ->assertSessionHasErrors(['message' => 'This lead does not have a WhatsApp number.']);
});

test('agent can preview and send a whatsapp template', function () {
    createTestTenant();
    $admin = actingAsTenantUser();

    $agentRole = Role::query()->where('slug', 'agent')->firstOrFail();
    $agent = User::query()->create([
        'name' => 'Agent User',
        'email' => 'agent@acme.test',
        'password' => 'password',
        'role_id' => $agentRole->id,
        'email_verified_at' => now(),
    ]);

    $lead = Lead::factory()->create([
        'name' => 'Meera Joshi',
        'phone' => '+91 9888877777',
        'assigned_to_id' => $agent->id,
    ]);
    $template = MessageTemplate::factory()->create([
        'created_by_id' => $admin->id,
        'name' => 'Agent hello',
        'body' => 'Hello {{lead.name}}',
    ]);

    actingAsTenantUser($agent);

    $this->getJson('/acme/leads/'.$lead->id.'/whatsapp')
        ->assertOk()
        ->assertJsonPath('templates.0.preview', 'Hello Meera Joshi');

    $this->from('/acme/leads')
        ->post('/acme/leads/'.$lead->id.'/whatsapp', [
            'template_id' => $template->id,
            'message' => 'Hello Meera Joshi',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('lead_activities', [
        'lead_id' => $lead->id,
        'type' => LeadActivityType::WhatsAppMessage->value,
        'description' => 'Hello Meera Joshi',
    ]);
});

test('agents cannot preview whatsapp for a lead assigned to someone else', function () {
    createTestTenant();
    $admin = actingAsTenantUser();

    $agentRole = Role::query()->where('slug', 'agent')->firstOrFail();
    $agent = User::query()->create([
        'name' => 'Agent User',
        'email' => 'agent@acme.test',
        'password' => 'password',
        'role_id' => $agentRole->id,
        'email_verified_at' => now(),
    ]);

    $lead = Lead::factory()->create([
        'phone' => '+91 9000000000',
        'assigned_to_id' => $admin->id,
    ]);

    actingAsTenantUser($agent);

    $this->getJson('/acme/leads/'.$lead->id.'/whatsapp')
        ->assertNotFound();
});
