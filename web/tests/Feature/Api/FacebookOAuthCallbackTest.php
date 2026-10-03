<?php

use App\Actions\StartFacebookOAuth;

test('facebook oauth callback redirects home when login is denied without state', function () {
    $this->get('/api/oauth/facebook/callback?error=access_denied&error_reason=user_denied')
        ->assertRedirect('/');
});

test('facebook oauth callback redirects to the facebook settings page when login is denied with state', function () {
    createTestTenant();

    $state = app(StartFacebookOAuth::class)->encodeState([
        'tenant_id' => 'acme',
        'connection_id' => 1,
        'page_id' => '123',
    ]);

    $this->from('/')
        ->get('/api/oauth/facebook/callback?'.http_build_query([
            'error' => 'access_denied',
            'error_reason' => 'user_denied',
            'state' => $state,
        ]))
        ->assertRedirect('/acme/settings/integrations/facebook')
        ->assertSessionHasErrors('verify');
});
