<?php

test('tenant sidebar shows bookings revenue with payouts and invoices submenu and integrations links', function () {
    createTestTenant();
    actingAsTenantUser();

    $this->get('/acme/dashboard')
        ->assertOk()
        ->assertSee('Bookings')
        ->assertSee('Revenue')
        ->assertSee('Payouts')
        ->assertSee('Invoices')
        ->assertSee('Integrations')
        ->assertSee(route('tenant.bookings.index', ['tenant' => 'acme'], false))
        ->assertSee(route('tenant.revenue.index', ['tenant' => 'acme'], false))
        ->assertSee(route('tenant.payouts.index', ['tenant' => 'acme'], false))
        ->assertSee(route('tenant.invoices.index', ['tenant' => 'acme'], false))
        ->assertSee(route('tenant.integrations.index', ['tenant' => 'acme'], false));
});

test('tenant users can view bookings home', function () {
    createTestTenant();
    actingAsTenantUser();

    $this->get('/acme/bookings')
        ->assertOk()
        ->assertSee('Bookings')
        ->assertSee('Create Booking')
        ->assertSee('Select a property')
        ->assertSee('No bookings yet.');
});

test('tenant users can view revenue home', function () {
    createTestTenant();
    actingAsTenantUser();

    $this->get('/acme/revenue')
        ->assertOk()
        ->assertSee('Revenue')
        ->assertSee('Total Revenue')
        ->assertSee('Revenue This Month')
        ->assertSee('Pending Commission');
});

test('tenant users can view invoices home', function () {
    createTestTenant();
    actingAsTenantUser();

    $this->get('/acme/invoices')
        ->assertOk()
        ->assertSee('Invoices')
        ->assertSee('All Invoices')
        ->assertSee('Actions')
        ->assertSee('No invoices yet.');
});

test('tenant users can view integrations home', function () {
    createTestTenant();
    actingAsTenantUser();

    $this->get('/acme/integrations')
        ->assertOk()
        ->assertSee('Integrations')
        ->assertSee('WhatsApp')
        ->assertSee('Email')
        ->assertSee('Calendar')
        ->assertSee('Coming soon');
});
