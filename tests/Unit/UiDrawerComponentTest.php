<?php

use App\View\Components\Ui\Drawer;
use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

uses(TestCase::class);

test('drawer defaults to right side', function () {
    $component = new Drawer;

    expect($component->side)->toBe('right')
        ->and($component->panelPositionClass())->toBe('inset-y-0 end-0');
});

test('drawer exposes alpine config', function () {
    $component = new Drawer(
        name: 'mobile-nav',
        show: true,
        side: 'left',
        closeUrl: '/leads',
        overlayOnly: true,
    );

    expect($component->config())
        ->toMatchArray([
            'name' => 'mobile-nav',
            'show' => true,
            'side' => 'left',
            'closeUrl' => '/leads',
            'overlayOnly' => true,
        ]);
});

test('drawer maps max width classes', function () {
    $component = new Drawer(maxWidth: '5xl');

    expect($component->maxWidthClass())->toBe('w-full max-w-5xl');

    $halfDrawer = new Drawer(maxWidth: 'half');

    expect($halfDrawer->maxWidthClass())->toContain('calc((100vw-var(--sidebar-width,16rem))/2)');

    $leftDrawer = new Drawer(side: 'left', maxWidth: 'sm');

    expect($leftDrawer->maxWidthClass())->toBe('w-64 max-w-[85vw]');
});

test('content inset positions drawer below header and beside sidebar', function () {
    $component = new Drawer(inset: 'content', side: 'right');

    expect($component->overlayPositionClass())
        ->toContain('top-14')
        ->toContain('lg:start-[var(--sidebar-width,16rem)]')
        ->and($component->panelPositionClass())->toBe('top-14 bottom-0 end-0');
});

test('drawer component renders overlay and panel', function () {
    $html = Blade::render(<<<'BLADE'
        <x-ui.drawer :show="true" side="right" max-width="lg">
            <p>Drawer content</p>
        </x-ui.drawer>
    BLADE);

    expect($html)
        ->toContain('uiDrawer')
        ->toContain('Drawer content')
        ->toContain('translate-x-full')
        ->toContain('backdrop-blur')
        ->toContain('x-teleport="body"');
});

test('overlay only drawer renders without panel', function () {
    $html = Blade::render(<<<'BLADE'
        <x-ui.drawer name="mobile-nav" side="left" overlay-only />
    BLADE);

    expect($html)
        ->toContain('mobile-nav')
        ->toContain('overlayOnly')
        ->not->toContain('role="dialog"');
});
