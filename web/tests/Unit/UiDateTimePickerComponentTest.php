<?php

use App\View\Components\Ui\DateTimePicker;
use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

uses(TestCase::class);

test('datetime picker defaults id to name', function () {

    $component = new DateTimePicker(name: 'due_at');

    expect($component->id)->toBe('due_at')

        ->and($component->mode)->toBe('datetime')

        ->and($component->value)->toBe('')

        ->and($component->inputType())->toBe('datetime-local')

        ->and($component->normalizedValue())->toBe('');

});

test('datetime picker normalizes datetime values for datetime-local input', function () {

    $component = new DateTimePicker(

        name: 'scheduled_at',

        mode: 'datetime',

        value: '2026-09-04 10:00:00',

    );

    expect($component->normalizedValue())->toBe('2026-09-04T10:00');

});

test('datetime picker component renders native date input', function () {

    $html = Blade::render(<<<'BLADE'

        <x-ui.datetime-picker

            id="booking_date"

            name="booking_date"

            mode="date"

            value="2026-09-02"

            required

        />

    BLADE);

    expect($html)

        ->toContain('type="date"')

        ->toContain('name="booking_date"')

        ->toContain('id="booking_date"')

        ->toContain('value="2026-09-02"')

        ->toContain('required');

});

test('datetime picker component renders native datetime-local input', function () {

    $html = Blade::render(<<<'BLADE'

        <x-ui.datetime-picker

            id="scheduled_at"

            name="scheduled_at"

            value="2026-09-04 10:00:00"

            required

        />

    BLADE);

    expect($html)

        ->toContain('type="datetime-local"')

        ->toContain('value="2026-09-04T10:00"');

});
