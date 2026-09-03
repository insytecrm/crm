<?php

use App\View\Components\Ui\Button;
use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

uses(TestCase::class);

test('default button uses primary styling', function () {
    $component = new Button;

    expect($component->classes()['classes'])
        ->toContain('bg-navy')
        ->toContain('text-white')
        ->toContain('rounded-lg')
        ->toContain('h-9');
});

test('destructive button uses danger styling', function () {
    $component = new Button(variant: 'destructive');

    expect($component->classes()['classes'])
        ->toContain('bg-red-600');
});

test('outline button uses border styling', function () {
    $component = new Button(variant: 'outline');

    expect($component->classes()['classes'])
        ->toContain('border')
        ->toContain('bg-white');
});

test('link button renders as anchor tag', function () {
    $component = new Button(variant: 'link', href: '/example');

    expect($component->classes()['tag'])->toBe('a');
});

test('button component renders in a blade view', function () {
    $html = Blade::render('<x-ui.button>Save</x-ui.button>');

    expect($html)
        ->toContain('Save')
        ->toContain('bg-navy');
});
