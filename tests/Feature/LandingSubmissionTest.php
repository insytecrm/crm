<?php

use App\Enums\LandingSubmissionType;
use App\Models\LandingSubmission;

test('welcome page renders landing mount point and meta', function () {
    $response = $this->get('/');

    $response->assertOk();
    $response->assertSee('id="landing-root"', false);
    $response->assertSee('InSyte CRM — Real Estate Channel Partner CRM | InSyte', false);
    $response->assertSee('data-demo-url=', false);
    $response->assertSee('data-trial-url=', false);
});

test('legal pages are accessible', function () {
    $this->get('/privacy')->assertOk()->assertSee('Privacy Policy');
    $this->get('/terms')->assertOk()->assertSee('Terms of Service');
    $this->get('/refund')->assertOk()->assertSee('Refund Policy');
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
        'type' => LandingSubmissionType::Demo->value,
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
    $response->assertJsonPath('message', "You're in! Check your email to activate your account.");

    expect(LandingSubmission::query()->where('email', 'priya@example.com')->first())
        ->type->toBe(LandingSubmissionType::Trial)
        ->billing_cycle->toBe('yearly');
});

test('demo submission validates required fields', function () {
    $response = $this->postJson(route('landing.demo.store'), []);

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['full_name', 'company', 'phone', 'email', 'team_size']);
});
