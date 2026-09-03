<?php

use App\View\Components\Ui\Combobox;
use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

uses(TestCase::class);

test('combobox defaults id to name', function () {
    $component = new Combobox(name: 'status');

    expect($component->id)->toBe('status');
});

test('combobox exposes alpine config', function () {
    $component = new Combobox(
        name: 'assigned_to_id',
        options: [
            ['value' => '', 'label' => 'Unassigned'],
            ['value' => '1', 'label' => 'Jane Doe'],
        ],
        value: '1',
        searchable: false,
        submitOnSelect: true,
    );

    expect($component->config())
        ->toMatchArray([
            'value' => '1',
            'searchable' => false,
            'submitOnSelect' => true,
        ])
        ->and($component->config()['options'])->toHaveCount(2);
});

test('combobox component renders hidden input and trigger', function () {
    $html = Blade::render(<<<'BLADE'
        <x-ui.combobox
            name="status"
            :options="[['value' => 'active', 'label' => 'Active']]"
            value="active"
            :searchable="false"
        />
    BLADE);

    expect($html)
        ->toContain('name="status"')
        ->toContain('uiCombobox')
        ->toContain('Active');
});

test('combobox component renders search input when searchable', function () {
    $html = Blade::render(<<<'BLADE'
        <x-ui.combobox
            name="assigned_to_id"
            :options="[['value' => '1', 'label' => 'Jane Doe']]"
        />
    BLADE);

    expect($html)->toContain('Search...');
});
