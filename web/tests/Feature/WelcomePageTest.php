<?php

test('welcome page renders site navigation and hero mount', function () {
    $response = $this->get('http://127.0.0.1/');

    $response->assertOk();
    $response->assertSee('id="welcome-root"', false);
    $response->assertSee('data-page="home"', false);
    $response->assertSee('data-logo-light=', false);
    $response->assertSee('data-logo-dark=', false);
    $response->assertSee('data-dashboard-src=', false);
    $response->assertSee('images/2.png', false);
    $response->assertSee('hero-dashboard.png', false);
    $response->assertSee('inSyte', false);
});

test('welcome page renders on localhost central domain', function () {
    $response = $this->get('http://localhost/');

    $response->assertOk();
    $response->assertSee('id="welcome-root"', false);
    $response->assertSee('data-page="home"', false);
});
