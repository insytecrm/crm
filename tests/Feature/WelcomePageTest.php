<?php

test('welcome page renders landing mount point', function () {
    $response = $this->get('/');

    $response->assertOk();
    $response->assertSee('id="landing-root"', false);
    $response->assertSee('InSyte CRM — Real Estate Channel Partner CRM | InSyte', false);
});
