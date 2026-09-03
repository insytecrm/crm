<?php

use App\View\Components\Ui\Popover;
use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

uses(TestCase::class);

test('popover defaults to bottom center', function () {
    $component = new Popover;

    expect($component->side)->toBe('bottom')
        ->and($component->align)->toBe('center')
        ->and($component->panelPositionClass())->toBe('absolute z-[70] top-full mt-2 start-1/2 -translate-x-1/2')
        ->and($component->originClass())->toBe('origin-top')
        ->and($component->widthClass())->toBe('w-72');
});

test('popover exposes alpine config', function () {
    $component = new Popover(show: true);

    expect($component->config())->toMatchArray([
        'open' => true,
    ]);
});

test('popover maps side and align classes', function () {
    $endPopover = new Popover(side: 'bottom', align: 'end');

    expect($endPopover->panelPositionClass())->toBe('absolute z-[70] top-full mt-2 end-0')
        ->and($endPopover->originClass())->toBe('origin-top-right');

    $topPopover = new Popover(side: 'top', align: 'start');

    expect($topPopover->panelPositionClass())->toBe('absolute z-[70] bottom-full mb-2 start-0')
        ->and($topPopover->originClass())->toBe('origin-bottom-left');

    $rightPopover = new Popover(side: 'right', align: 'start', width: '56');

    expect($rightPopover->panelPositionClass())->toBe('absolute z-[70] start-full ms-2 top-0')
        ->and($rightPopover->widthClass())->toBe('w-56');
});

test('popover component renders trigger and content', function () {
    $html = Blade::render(<<<'BLADE'
        <x-ui.popover align="end" width="56">
            <x-slot:trigger>
                <button type="button">Open</button>
            </x-slot:trigger>
            <p>Popover content</p>
        </x-ui.popover>
    BLADE);

    expect($html)
        ->toContain('uiPopover')
        ->toContain('Open')
        ->toContain('Popover content')
        ->toContain('role="dialog"')
        ->toContain('w-56');
});

test('popover closes on content click when enabled', function () {
    $html = Blade::render(<<<'BLADE'
        <x-ui.popover close-on-content-click>
            <x-slot:trigger>
                <button type="button">Open</button>
            </x-slot:trigger>
            Menu
        </x-ui.popover>
    BLADE);

    expect($html)->toContain('@click="close()"');
});

test('popover does not close on content click by default', function () {
    $html = Blade::render(<<<'BLADE'
        <x-ui.popover>
            <x-slot:trigger>
                <button type="button">Open</button>
            </x-slot:trigger>
            Menu
        </x-ui.popover>
    BLADE);

    expect($html)->not->toContain('@click="close()"');
});

test('popover sidebar variant uses sidebar accent panel styles', function () {
    $component = new Popover(variant: 'sidebar');

    expect($component->panelClass())->toBe('border-sidebar-border bg-sidebar-accent text-white shadow-sm shadow-slate-900/5');

    $html = Blade::render(<<<'BLADE'
        <x-ui.popover variant="sidebar">
            <x-slot:trigger>
                <button type="button">Open</button>
            </x-slot:trigger>
            <x-ui.popover.title variant="sidebar">Jane Doe</x-ui.popover.title>
            <x-ui.popover.item variant="sidebar" href="#">Profile</x-ui.popover.item>
        </x-ui.popover>
    BLADE);

    expect($html)
        ->toContain('bg-sidebar-accent')
        ->toContain('text-white')
        ->toContain('hover:bg-white/10');
});
