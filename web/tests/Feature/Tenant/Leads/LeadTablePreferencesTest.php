<?php

use App\Enums\LeadListingFilter;
use App\Support\LeadTablePreferences;

test('tenant users can persist lead table column preferences', function () {
    createTestTenant();
    $user = actingAsTenantUser();

    $this->patchJson('/acme/leads/table-preferences', [
        'listing' => LeadListingFilter::All->value,
        'columns' => [
            'name' => true,
            'phone' => false,
            'source' => true,
            'requirement' => true,
            'assigned_to' => true,
            'status' => true,
            'next_follow_up' => true,
            'follow_ups_count' => true,
            'site_visits_count' => false,
            'last_activity' => false,
            'property_interest' => false,
            'created_at' => true,
            'actions' => true,
        ],
        'actions' => [
            'call' => false,
            'whatsapp' => true,
            'follow_up' => true,
            'site_visit' => false,
            'create_booking' => true,
        ],
    ])
        ->assertOk()
        ->assertJsonPath('columns.phone', false)
        ->assertJsonPath('columns.source', true)
        ->assertJsonPath('actions.call', false)
        ->assertJsonPath('actions.site_visit', false);

    $user->refresh();

    expect($user->leadTablePreferences(LeadListingFilter::All)['columns']['phone'])->toBeFalse()
        ->and($user->leadTablePreferences(LeadListingFilter::All)['actions']['call'])->toBeFalse();
});

test('lead table column preferences are stored separately per listing', function () {
    createTestTenant();
    $user = actingAsTenantUser();

    $this->patchJson('/acme/leads/table-preferences', [
        'listing' => LeadListingFilter::All->value,
        'columns' => array_merge(LeadTablePreferences::defaults()['columns'], [
            'phone' => false,
        ]),
        'actions' => LeadTablePreferences::defaults()['actions'],
    ])->assertOk();

    $this->patchJson('/acme/leads/table-preferences', [
        'listing' => LeadListingFilter::Priority->value,
        'columns' => array_merge(LeadTablePreferences::defaults()['columns'], [
            'phone' => true,
            'source' => false,
        ]),
        'actions' => LeadTablePreferences::defaults()['actions'],
    ])->assertOk();

    $user->refresh();

    expect($user->leadTablePreferences(LeadListingFilter::All)['columns']['phone'])->toBeFalse()
        ->and($user->leadTablePreferences(LeadListingFilter::All)['columns']['source'])->toBeTrue()
        ->and($user->leadTablePreferences(LeadListingFilter::Priority)['columns']['phone'])->toBeTrue()
        ->and($user->leadTablePreferences(LeadListingFilter::Priority)['columns']['source'])->toBeFalse();
});

test('leads page loads saved table preferences for the authenticated user', function () {
    createTestTenant();
    $user = actingAsTenantUser();

    $preferences = $user->preferences ?? [];
    $preferences[LeadTablePreferences::StorageKey] = [
        LeadListingFilter::All->value => LeadTablePreferences::normalize([
            'columns' => [
                'name' => true,
                'phone' => false,
                'source' => true,
                'requirement' => true,
                'assigned_to' => true,
                'status' => true,
                'next_follow_up' => true,
                'follow_ups_count' => false,
                'site_visits_count' => false,
                'last_activity' => true,
                'property_interest' => false,
                'created_at' => true,
                'actions' => true,
            ],
            'actions' => LeadTablePreferences::defaults()['actions'],
        ]),
    ];

    $user->forceFill(['preferences' => $preferences])->save();

    $this->get('/acme/leads')
        ->assertOk()
        ->assertViewHas('leadTablePreferences', fn (array $preferences): bool => $preferences['columns']['phone'] === false)
        ->assertViewHas('leadListingKey', LeadListingFilter::All->value);
});

test('priority leads page loads its own saved table preferences', function () {
    createTestTenant();
    $user = actingAsTenantUser();

    $preferences = $user->preferences ?? [];
    $preferences[LeadTablePreferences::StorageKey] = [
        LeadListingFilter::All->value => LeadTablePreferences::normalize([
            'columns' => array_merge(LeadTablePreferences::defaults()['columns'], [
                'phone' => false,
            ]),
            'actions' => LeadTablePreferences::defaults()['actions'],
        ]),
        LeadListingFilter::Priority->value => LeadTablePreferences::normalize([
            'columns' => array_merge(LeadTablePreferences::defaults()['columns'], [
                'phone' => true,
                'source' => false,
            ]),
            'actions' => LeadTablePreferences::defaults()['actions'],
        ]),
    ];

    $user->forceFill(['preferences' => $preferences])->save();

    $this->get('/acme/leads/priority')
        ->assertOk()
        ->assertViewHas('leadTablePreferences', fn (array $preferences): bool => $preferences['columns']['phone'] === true
            && $preferences['columns']['source'] === false)
        ->assertViewHas('leadListingKey', LeadListingFilter::Priority->value);
});

test('legacy lead table preferences are migrated to the all leads listing', function () {
    createTestTenant();
    $user = actingAsTenantUser();

    $preferences = $user->preferences ?? [];
    $preferences[LeadTablePreferences::StorageKey] = LeadTablePreferences::normalize([
        'columns' => array_merge(LeadTablePreferences::defaults()['columns'], [
            'phone' => false,
        ]),
        'actions' => LeadTablePreferences::defaults()['actions'],
    ]);

    $user->forceFill(['preferences' => $preferences])->save();

    expect($user->leadTablePreferences(LeadListingFilter::All)['columns']['phone'])->toBeFalse()
        ->and($user->leadTablePreferences(LeadListingFilter::Priority))->toBe(LeadTablePreferences::defaults());
});

test('resetting lead table preferences stores defaults for the user', function () {
    createTestTenant();
    $user = actingAsTenantUser();

    $this->patchJson('/acme/leads/table-preferences', [
        'listing' => LeadListingFilter::All->value,
        'columns' => array_merge(LeadTablePreferences::defaults()['columns'], [
            'phone' => false,
            'source' => false,
        ]),
        'actions' => LeadTablePreferences::defaults()['actions'],
    ])->assertOk();

    $this->patchJson('/acme/leads/table-preferences', [
        'listing' => LeadListingFilter::All->value,
        'columns' => LeadTablePreferences::defaults()['columns'],
        'actions' => LeadTablePreferences::defaults()['actions'],
    ])->assertOk();

    $user->refresh();

    expect($user->leadTablePreferences(LeadListingFilter::All))->toBe(LeadTablePreferences::defaults());
});
