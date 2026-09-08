<?php

use App\Enums\MessageTemplateChannel;
use App\Enums\TenantPermission;
use App\Models\MessageTemplate;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;

test('guests are redirected from the templates page', function () {
    createTestTenant();

    $this->get('/acme/automations/templates')
        ->assertRedirect(route('tenant.login', ['tenant' => 'acme']));
});

test('tenant users can view the templates list', function () {
    createTestTenant();
    $user = actingAsTenantUser();

    MessageTemplate::factory()->create([
        'created_by_id' => $user->id,
        'name' => 'Site visit reminder',
        'body' => 'Hi {{lead.name}}, see you at the site visit.',
    ]);

    $this->get('/acme/automations/templates')
        ->assertOk()
        ->assertSee('Templates')
        ->assertSee('Create template')
        ->assertSee('Site visit reminder')
        ->assertSee('WhatsApp')
        ->assertSee('{{lead.name}}')
        ->assertDontSee('Coming soon');
});

test('template names are escaped on the templates list', function () {
    createTestTenant();
    $user = actingAsTenantUser();

    MessageTemplate::factory()->create([
        'created_by_id' => $user->id,
        'name' => '<script>alert("xss")</script>',
        'body' => '<img src=x onerror=alert(1)>',
    ]);

    $this->get('/acme/automations/templates')
        ->assertOk()
        ->assertDontSee('<script>alert("xss")</script>', false)
        ->assertDontSee('<img src=x onerror=alert(1)>', false)
        ->assertSee('&lt;script&gt;alert(&quot;xss&quot;)&lt;/script&gt;', false);
});

test('agent cannot view templates', function () {
    createTestTenant();
    actingAsTenantUser();

    $agentRole = Role::query()->where('slug', 'agent')->firstOrFail();
    $agent = User::query()->create([
        'name' => 'Agent User',
        'email' => 'agent-templates@acme.test',
        'password' => 'password',
        'role_id' => $agentRole->id,
        'email_verified_at' => now(),
    ]);

    actingAsTenantUser($agent);

    $this->get('/acme/automations/templates')->assertForbidden();
    $this->get('/acme/automations/templates/create')->assertForbidden();
});

test('users without manage permission can view templates but cannot create them', function () {
    createTestTenant();
    actingAsTenantUser();

    $role = Role::factory()->create(['name' => 'Template Viewer']);
    $role->permissions()->sync(
        Permission::query()->whereIn('key', [
            TenantPermission::DashboardView->value,
            TenantPermission::AutomationsView->value,
        ])->pluck('id'),
    );

    $viewer = User::query()->create([
        'name' => 'Template Viewer',
        'email' => 'template-viewer@acme.test',
        'password' => 'password',
        'role_id' => $role->id,
        'email_verified_at' => now(),
    ]);

    actingAsTenantUser($viewer);

    $this->get('/acme/automations/templates')
        ->assertOk()
        ->assertDontSee('Create template');

    $this->get('/acme/automations/templates/create')->assertForbidden();
    $this->post('/acme/automations/templates', [
        'name' => 'Blocked',
        'channel' => MessageTemplateChannel::WhatsApp->value,
        'body' => 'Hi {{lead.name}}',
    ])->assertForbidden();
});

test('create template page shows variable groups', function () {
    createTestTenant();
    actingAsTenantUser();

    $this->get('/acme/automations/templates/create')
        ->assertOk()
        ->assertSee('Create template')
        ->assertSee('Variables')
        ->assertSee('Lead')
        ->assertSee('Assigned user')
        ->assertSee('Current user')
        ->assertSee('Company')
        ->assertSee('Property')
        ->assertSee('Booking')
        ->assertSee('{{lead.name}}', false)
        ->assertSee('{{property.project_name}}', false);
});

test('tenant users can create a whatsapp template with variables', function () {
    createTestTenant();
    $user = actingAsTenantUser();

    $this->post('/acme/automations/templates', [
        'name' => 'Welcome WhatsApp',
        'channel' => MessageTemplateChannel::WhatsApp->value,
        'body' => 'Hi {{lead.name}}, this is {{user.name}} from {{company.name}}.',
        'is_active' => '1',
    ])
        ->assertRedirect(route('tenant.automations.templates', ['tenant' => 'acme']))
        ->assertSessionHas('status', 'Template created.');

    $template = MessageTemplate::query()->first();

    expect($template)->not->toBeNull()
        ->and($template->name)->toBe('Welcome WhatsApp')
        ->and($template->channel)->toBe(MessageTemplateChannel::WhatsApp)
        ->and($template->subject)->toBeNull()
        ->and($template->body)->toBe('Hi {{lead.name}}, this is {{user.name}} from {{company.name}}.')
        ->and($template->isActive())->toBeTrue()
        ->and($template->created_by_id)->toBe($user->id);
});

test('email templates require a subject', function () {
    createTestTenant();
    actingAsTenantUser();

    $this->from('/acme/automations/templates/create')
        ->post('/acme/automations/templates', [
            'name' => 'Follow-up email',
            'channel' => MessageTemplateChannel::Email->value,
            'body' => 'Hi {{lead.name}}',
        ])
        ->assertRedirect('/acme/automations/templates/create')
        ->assertSessionHasErrors(['subject' => 'Add a subject for email templates.']);

    expect(MessageTemplate::query()->count())->toBe(0);
});

test('empty templates are rejected', function () {
    createTestTenant();
    actingAsTenantUser();

    $this->from('/acme/automations/templates/create')
        ->post('/acme/automations/templates', [])
        ->assertRedirect('/acme/automations/templates/create')
        ->assertSessionHasErrors([
            'name' => 'Please name this template.',
            'channel' => 'Choose WhatsApp, Email, or SMS.',
            'body' => 'Write the template message.',
        ]);
});

test('unknown variables are rejected', function () {
    createTestTenant();
    actingAsTenantUser();

    $this->from('/acme/automations/templates/create')
        ->post('/acme/automations/templates', [
            'name' => 'Bad variable',
            'channel' => MessageTemplateChannel::WhatsApp->value,
            'body' => 'Hi {{lead.secret}}',
        ])
        ->assertRedirect('/acme/automations/templates/create')
        ->assertSessionHasErrors(['body' => 'Unknown variable {{lead.secret}}. Choose a variable from the list.']);

    expect(MessageTemplate::query()->count())->toBe(0);
});

test('tenant users can update a template', function () {
    createTestTenant();
    $user = actingAsTenantUser();

    $template = MessageTemplate::factory()->create([
        'created_by_id' => $user->id,
        'name' => 'Old name',
        'body' => 'Hi {{lead.name}}',
    ]);

    $this->patch('/acme/automations/templates/'.$template->id, [
        'name' => 'Updated name',
        'channel' => MessageTemplateChannel::Sms->value,
        'body' => 'Hello {{lead.name}} from {{property.project_name}}',
        'is_active' => '0',
    ])
        ->assertRedirect(route('tenant.automations.templates.edit', ['tenant' => 'acme', 'template' => $template->id]))
        ->assertSessionHas('status', 'Template saved.');

    $template->refresh();

    expect($template->name)->toBe('Updated name')
        ->and($template->channel)->toBe(MessageTemplateChannel::Sms)
        ->and($template->body)->toBe('Hello {{lead.name}} from {{property.project_name}}')
        ->and($template->isActive())->toBeFalse();
});

test('tenant users can delete a template', function () {
    createTestTenant();
    $user = actingAsTenantUser();

    $template = MessageTemplate::factory()->create([
        'created_by_id' => $user->id,
        'name' => 'Remove me',
    ]);

    $this->delete('/acme/automations/templates/'.$template->id)
        ->assertRedirect(route('tenant.automations.templates', ['tenant' => 'acme']))
        ->assertSessionHas('status', 'Template deleted.');

    $this->assertModelMissing($template);
});

test('templates list can filter by channel', function () {
    createTestTenant();
    $user = actingAsTenantUser();

    MessageTemplate::factory()->create([
        'created_by_id' => $user->id,
        'name' => 'WhatsApp only',
        'channel' => MessageTemplateChannel::WhatsApp,
    ]);
    MessageTemplate::factory()->email()->create([
        'created_by_id' => $user->id,
        'name' => 'Email only',
    ]);

    $this->get('/acme/automations/templates?filter=email')
        ->assertOk()
        ->assertSee('Email only')
        ->assertDontSee('WhatsApp only');
});
