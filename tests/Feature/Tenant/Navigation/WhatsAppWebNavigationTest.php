<?php

test('tenant sidebar shows whatsapp web menu below tasks', function () {
    createTestTenant();
    actingAsTenantUser();

    $this->get('/acme/dashboard')
        ->assertOk()
        ->assertSee('WhatsApp Web')
        ->assertSee(route('tenant.whatsapp-web.index', ['tenant' => 'acme'], false));
});

test('tenant whatsapp web menu redirects to official whatsapp web', function () {
    createTestTenant();
    actingAsTenantUser();

    $this->get('/acme/whatsapp-web')
        ->assertRedirect('https://web.whatsapp.com/');

    $this->get('/acme/whatsapp-web?phone=919000000000&message=Hello')
        ->assertRedirect('https://web.whatsapp.com/send?phone=919000000000&text=Hello');
});
