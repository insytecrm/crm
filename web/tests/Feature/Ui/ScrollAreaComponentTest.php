<?php

use App\Models\Lead;

test('tenant layout renders shadcn-style scroll area regions', function () {
    createTestTenant();
    actingAsTenantUser();

    $this->get('/acme/dashboard')
        ->assertOk()
        ->assertSee('data-slot="scroll-area"', false)
        ->assertSee('data-slot="scroll-area-viewport"', false);
});

test('lead drawer renders scroll area for tab panels', function () {
    createTestTenant();
    actingAsTenantUser();

    $lead = Lead::factory()->create(['name' => 'Scroll Lead']);

    $this->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
        ->get('/acme/leads/'.$lead->id)
        ->assertOk()
        ->assertSee('data-slot="scroll-area"', false)
        ->assertSee('Activity Timeline');
});
