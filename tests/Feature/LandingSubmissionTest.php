<?php

test('welcome page renders site mount with home page data', function () {
    $response = $this->get('http://127.0.0.1/');

    $response->assertOk();
    $response->assertSee('id="welcome-root"', false);
    $response->assertSee('data-page="home"', false);
    $response->assertSee('data-demo-url=', false);
    $response->assertSee('data-trial-url=', false);
    $response->assertSee('data-logo-light=', false);
    $response->assertSee('hero-dashboard.png', false);
});

test('marketing product and solutions pages render', function () {
    $paths = [
        '/crm',
        '/automation',
        '/ai',
        '/integrations',
        '/customization',
        '/solutions/real-estate',
        '/solutions/sales-teams',
        '/pricing',
        '/demo',
        '/signup',
        '/faqs',
        '/help',
        '/help/add-a-lead',
        '/docs',
        '/docs/lead-api',
        '/guides',
        '/guides/real-estate-lead-management',
        '/blog',
        '/about',
        '/contact',
        '/careers',
    ];

    foreach ($paths as $path) {
        $this->get('http://127.0.0.1'.$path)
            ->assertOk()
            ->assertSee('id="welcome-root"', false);
    }
});

test('legal pages are accessible', function () {
    $this->get('/privacy')->assertOk()->assertSee('Privacy Policy');
    $this->get('/terms')->assertOk()->assertSee('Terms of Service');
    $this->get('/refund')->assertOk()->assertSee('Refund Policy');
    $this->get('/cookies')->assertOk()->assertSee('Cookie Policy');
    $this->get('/security')->assertOk()->assertSee('Data Processing');
});

test('demo submission is stored', function () {
    $response = $this->postJson(route('landing.demo.store'), [
        'full_name' => 'Rajesh Kumar',
        'company' => 'Pune Properties',
        'phone' => '9876543210',
        'email' => 'rajesh@example.com',
        'team_size' => '2_5',
        'plan' => 'growth',
    ]);

    $response->assertOk();
    $response->assertJsonPath('message', 'Thank you! Our team will call you within 24 hours.');

    $this->assertDatabaseHas('landing_submissions', [
        'email' => 'rajesh@example.com',
        'plan' => 'growth',
    ]);
});

test('trial submission is stored', function () {
    $response = $this->postJson(route('landing.trial.store'), [
        'full_name' => 'Priya Shah',
        'company' => 'Mumbai Realty',
        'phone' => '9123456780',
        'email' => 'priya@example.com',
        'team_size' => '6_15',
        'plan' => 'pro',
        'billing_cycle' => 'yearly',
    ]);

    $response->assertOk();
});

test('demo submission validates required fields', function () {
    $response = $this->postJson(route('landing.demo.store'), []);

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['full_name', 'company', 'phone', 'email', 'team_size']);
});
