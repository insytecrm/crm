<?php

test('tenant sidebar shows whatsapp menu with triggers submenu', function () {
    createTestTenant();
    actingAsTenantUser();

    $this->get('/acme/dashboard')
        ->assertOk()
        ->assertSee('WhatsApp')
        ->assertSee('https://web.whatsapp.com/')
        ->assertSee('Triggers')
        ->assertSee(route('tenant.whatsapp-triggers.index', ['tenant' => 'acme'], false));
});

test('tenant whatsapp web menu redirects to official whatsapp web', function () {
    createTestTenant();
    actingAsTenantUser();

    $this->get('/acme/whatsapp-web')
        ->assertRedirect('https://web.whatsapp.com/');

    $this->get('/acme/whatsapp-web?phone=919000000000&message=Hello')
        ->assertRedirect('https://web.whatsapp.com/send?phone=919000000000&text=Hello');
});
