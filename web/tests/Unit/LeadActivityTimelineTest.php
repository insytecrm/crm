<?php

use App\Enums\LeadActivityType;
use App\Models\LeadActivity;
use App\Models\LeadNote;
use App\Models\User;
use Tests\TestCase;

uses(TestCase::class);

test('timeline hover detail returns note body for note added activities', function () {
    $activity = new LeadActivity([
        'type' => LeadActivityType::NoteAdded,
        'description' => 'Note added',
        'metadata' => ['note_id' => 1, 'body' => 'Interested in sea-facing units.'],
    ]);

    expect($activity->timelineHoverDetail())->toBe('Interested in sea-facing units.');
});

test('timeline hover detail prefers linked note body over metadata', function () {
    $activity = new LeadActivity([
        'type' => LeadActivityType::NoteAdded,
        'description' => 'Note added',
        'metadata' => ['note_id' => 1, 'body' => 'Stored snapshot'],
    ]);

    $note = new LeadNote([
        'id' => 1,
        'body' => 'Latest note text',
    ]);

    expect($activity->timelineHoverDetail($note))->toBe('Latest note text');
});

test('timeline hover detail returns activity description for scheduled events', function () {
    $activity = new LeadActivity([
        'type' => LeadActivityType::SiteVisitScheduled,
        'description' => '1st Site Visit scheduled for Mar 1, 2026 2:00 PM — Meet at gate',
    ]);

    expect($activity->timelineHoverDetail())->toBe('1st Site Visit scheduled for Mar 1, 2026 2:00 PM — Meet at gate');
});

test('timeline hover detail falls back to user name for generic activities', function () {
    $user = new User(['name' => 'Jane Agent']);

    $activity = new LeadActivity([
        'type' => LeadActivityType::CallMade,
        'description' => 'Call Made',
    ]);
    $activity->setRelation('user', $user);

    expect($activity->timelineHoverDetail())->toBe('By Jane Agent');
});
