<?php

test('meta lead webhook verifies with the configured token and returns the challenge', function () {
    config(['services.meta.verify_token' => 'insyte_meta_verify_local_9f2k7m']);

    $this->get('/api/webhooks/meta/leads?'.http_build_query([
        'hub.mode' => 'subscribe',
        'hub.verify_token' => 'insyte_meta_verify_local_9f2k7m',
        'hub.challenge' => '1234567890',
    ]))
        ->assertOk()
        ->assertSee('1234567890', false);
});

test('meta lead webhook rejects an invalid verify token', function () {
    config(['services.meta.verify_token' => 'insyte_meta_verify_local_9f2k7m']);

    $this->get('/api/webhooks/meta/leads?'.http_build_query([
        'hub.mode' => 'subscribe',
        'hub.verify_token' => 'wrong-token',
        'hub.challenge' => '1234567890',
    ]))->assertForbidden();
});

test('meta lead webhook acknowledges posted events when no app secret is configured', function () {
    config([
        'services.meta.verify_token' => 'insyte_meta_verify_local_9f2k7m',
        'services.meta.app_secret' => null,
    ]);

    $this->postJson('/api/webhooks/meta/leads', [
        'object' => 'page',
        'entry' => [
            ['id' => '123', 'time' => 1, 'changes' => []],
        ],
    ])
        ->assertOk()
        ->assertSee('EVENT_RECEIVED', false);
});

test('meta lead webhook rejects posts with an invalid signature when app secret is set', function () {
    config([
        'services.meta.verify_token' => 'insyte_meta_verify_local_9f2k7m',
        'services.meta.app_secret' => 'test-app-secret',
    ]);

    $this->postJson('/api/webhooks/meta/leads', [
        'object' => 'page',
        'entry' => [],
    ], [
        'X-Hub-Signature-256' => 'sha256=invalid',
    ])->assertForbidden();
});
