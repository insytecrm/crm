<?php

use App\Models\User;

test('login screen can be rendered', function () {
    $this->get('/platform/login')
        ->assertSee('Welcome Back')
        ->assertSee('Platform admin')
        ->assertSee('Sign In')
        ->assertSee('Forgot your password?')
        ->assertSee('/images/2.png')
        ->assertSee('alt="InSyte CRM"', false)
        ->assertDontSee('Choose your role');
});

test('super admins can authenticate using the login screen', function () {
    $user = User::factory()->superAdmin()->create();

    $response = $this->post('/platform/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('platform.dashboard', absolute: false));
});

test('users can not authenticate with invalid password', function () {
    $user = User::factory()->superAdmin()->create();

    $this->post('/platform/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);

    $this->assertGuest();
});

test('non super admins cannot authenticate on the platform', function () {
    $user = User::factory()->create();

    $this->post('/platform/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertGuest();
});

test('users can logout', function () {
    $user = User::factory()->superAdmin()->create();

    $response = $this->actingAs($user)->post('/platform/logout');

    $this->assertGuest();
    $response->assertRedirect(route('login'));
});
