<?php

test('tenant sidebar shows automations workflows and templates submenu links', function () {
    createTestTenant();
    actingAsTenantUser();

    $this->get('/acme/dashboard')
        ->assertOk()
        ->assertSee('Automations')
        ->assertSee('Workflows')
        ->assertSee('Templates')
        ->assertSee(route('tenant.automations.index', ['tenant' => 'acme'], false))
        ->assertSee(route('tenant.automations.workflows', ['tenant' => 'acme'], false))
        ->assertSee(route('tenant.automations.templates', ['tenant' => 'acme'], false));
});

test('tenant users can view automations home', function () {
    createTestTenant();
    actingAsTenantUser();

    $this->get('/acme/automations')
        ->assertOk()
        ->assertSee('Automations')
        ->assertSee('Coming soon');
});

test('tenant users can view automations workflows submenu', function () {
    createTestTenant();
    actingAsTenantUser();

    $this->get('/acme/automations/workflows')
        ->assertOk()
        ->assertSee('Workflows')
        ->assertSee('Create workflow')
        ->assertDontSee('Coming soon');
});

test('tenant users can view automations templates submenu', function () {
    createTestTenant();
    actingAsTenantUser();

    $this->get('/acme/automations/templates')
        ->assertOk()
        ->assertSee('Templates')
        ->assertSee('Create template')
        ->assertDontSee('Coming soon');
});
