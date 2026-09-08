<?php

use App\Support\TemplateVariableCatalog;
use Tests\TestCase;

uses(TestCase::class);

test('catalog includes lead user property company and booking variables', function () {
    $keys = TemplateVariableCatalog::keys();

    expect($keys)
        ->toContain('lead.name')
        ->toContain('lead.phone')
        ->toContain('lead.status')
        ->toContain('assigned.name')
        ->toContain('user.email')
        ->toContain('company.name')
        ->toContain('property.project_name')
        ->toContain('property.microsite_url')
        ->toContain('booking.unit_number');
});

test('catalog groups are labeled for the variable picker', function () {
    $labels = collect(TemplateVariableCatalog::groups())->pluck('label')->all();

    expect($labels)->toBe([
        'Lead',
        'Assigned user',
        'Current user',
        'Company',
        'Property',
        'Booking',
    ]);
});

test('catalog extracts unknown tokens from template text', function () {
    expect(TemplateVariableCatalog::unknownKeysIn('Hi {{lead.name}} and {{lead.secret}}'))
        ->toBe(['lead.secret'])
        ->and(TemplateVariableCatalog::has('lead.name'))->toBeTrue()
        ->and(TemplateVariableCatalog::has('lead.secret'))->toBeFalse()
        ->and(TemplateVariableCatalog::token('lead.name'))->toBe('{{lead.name}}');
});
