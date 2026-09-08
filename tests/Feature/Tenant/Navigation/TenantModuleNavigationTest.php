<?php

test('tenant sidebar shows bookings revenue invoices reports analytics submenu links', function () {
    createTestTenant();
    actingAsTenantUser();

    $this->get('/acme/dashboard')
        ->assertOk()
        ->assertSee('Bookings')
        ->assertSee('Revenue')
        ->assertSee('Invoices')
        ->assertDontSee('Payouts')
        ->assertSee('Reports')
        ->assertSee('Analytics')
        ->assertDontSee(route('tenant.integrations.index', ['tenant' => 'acme'], false))
        ->assertSee(route('tenant.bookings.index', ['tenant' => 'acme'], false))
        ->assertSee(route('tenant.revenue.index', ['tenant' => 'acme'], false))
        ->assertSee(route('tenant.invoices.index', ['tenant' => 'acme'], false))
        ->assertSee(route('tenant.reports.index', ['tenant' => 'acme'], false))
        ->assertSee(route('tenant.reports.analytics', ['tenant' => 'acme'], false));
});

test('tenant sidebar divides menus with separators', function () {
    createTestTenant();
    actingAsTenantUser();

    $response = $this->get('/acme/dashboard')->assertOk();

    expect(substr_count($response->getContent(), 'role="separator"'))->toBe(3);
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
        ->assertSee('Total Sales')
        ->assertSee('Sales This Month')
        ->assertSee('Pending Commission');
});

test('tenant users can view reports home', function () {
    createTestTenant();
    actingAsTenantUser();

    $this->get('/acme/reports')
        ->assertOk()
        ->assertSee('Reports')
        ->assertSee('Total Leads')
        ->assertSee('Activity Trend')
        ->assertSee('Leads by Status');
});

test('tenant users can view analytics submenu', function () {
    createTestTenant();
    actingAsTenantUser();

    $this->get('/acme/reports/analytics')
        ->assertOk()
        ->assertSee('Leads per Day')
        ->assertSee('Conversion Rate')
        ->assertSee('Follow-up Rate')
        ->assertDontSee('Coming soon')
        ->assertDontSee('Total Leads');
});

test('tenant users can view invoices home', function () {
    createTestTenant();
    actingAsTenantUser();

    $this->get('/acme/invoices')
        ->assertOk()
        ->assertSee('Invoices')
        ->assertSee('All Invoices')
        ->assertSee('Create Invoice')
        ->assertSee('Filters')
        ->assertSee('Actions')
        ->assertSee('No invoices yet.');
});

test('tenant users can view integrations settings tab', function () {
    createTestTenant();
    actingAsTenantUser();

    $this->get('/acme/integrations')
        ->assertRedirect('/acme/settings?tab=integrations');

    $this->get('/acme/settings?tab=integrations')
        ->assertOk()
        ->assertSee('Integrations')
        ->assertSee('WhatsApp')
        ->assertSee('Email')
        ->assertSee('Calendar')
        ->assertSee('Coming soon');
});
