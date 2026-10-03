<?php

use App\Enums\LeadActivityType;
use App\Enums\LeadStatus;
use App\Models\Lead;
use App\Models\LeadActivity;

test('tenant users can log a lead interaction and auto advance status', function () {
    createTestTenant();
    actingAsTenantUser();

    $lead = Lead::factory()->create([
        'status' => LeadStatus::New,
        'name' => 'Interaction Lead',
    ]);

    $this->post('/acme/leads/'.$lead->id.'/interactions', [
        'interaction_type' => 'call',
        'body' => 'Discussed budget and location.',
    ])->assertRedirect();

    $lead->refresh();

    expect($lead->status)->toBe(LeadStatus::Contacted);

    expect(LeadActivity::query()
        ->where('lead_id', $lead->id)
        ->where('type', LeadActivityType::CallMade)
        ->exists())->toBeTrue();
});

test('lead drawer shows progress tab by default', function () {
    createTestTenant();
    actingAsTenantUser();

    $lead = Lead::factory()->create(['name' => 'Progress Tab Lead']);

    $this->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
        ->get('/acme/leads/'.$lead->id)
        ->assertOk()
        ->assertSee('Progress', false)
        ->assertSee('Lead journey', false)
        ->assertSee('log-interaction-'.$lead->id, false);
});
