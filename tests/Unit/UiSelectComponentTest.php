<?php

use App\View\Components\Ui\Select;
use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

uses(TestCase::class);

test('select defaults id to name', function () {
    $component = new Select(name: 'status');

    expect($component->id)->toBe('status');
});

test('select puts id on the trigger button for label association', function () {
    $html = Blade::render(<<<'BLADE'
        <x-ui.select
            id="property_type"
            name="property_type"
            :options="[['value' => 'apartment', 'label' => 'Apartment']]"
        />
    BLADE);

    expect($html)
        ->toContain('name="property_type"')
        ->toContain('uiSelect')
        ->toMatch('/<button[^>]*\bid="property_type"/')
        ->not->toMatch('/<input[^>]*type="hidden"[^>]*\bid="property_type"/')
        ->not->toMatch('/<input[^>]*\bid="property_type"[^>]*type="hidden"/');
});
