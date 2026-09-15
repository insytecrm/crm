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
        ->toContain('lead.next_follow_up_date')
        ->toContain('lead.upcoming_site_visit_time')
        ->toContain('assigned.name')
        ->toContain('assigned.phone')
        ->toContain('user.email')
        ->toContain('user.phone')
        ->toContain('company.name')
        ->toContain('property.project_name')
        ->toContain('property.microsite_url')
        ->toContain('booking.unit_number')
        ->toContain('booking.property')
        ->toContain('follow_up.upcoming.date')
        ->toContain('follow_up.completed.time')
        ->toContain('site_visit.upcoming.date')
        ->toContain('site_visit.previous.time')
        ->toContain('site_visit.completed.outcome');
});

test('catalog groups are labeled for the variable picker', function () {
    $labels = collect(TemplateVariableCatalog::groups())->pluck('label')->all();

    expect($labels)->toContain('Lead')
        ->toContain('Assigned user')
        ->toContain('Current user')
        ->toContain('Company')
        ->toContain('Property')
        ->toContain('Booking')
        ->toContain('Follow-up · Upcoming')
        ->toContain('Follow-up · Previous')
        ->toContain('Follow-up · Completed')
        ->toContain('Site visit · Upcoming')
        ->toContain('Site visit · Previous')
        ->toContain('Site visit · Completed');
});

test('catalog extracts unknown tokens from template text', function () {
    expect(TemplateVariableCatalog::unknownKeysIn('Hi {{lead.name}} and {{lead.secret}}'))
        ->toBe(['lead.secret'])
        ->and(TemplateVariableCatalog::has('lead.name'))->toBeTrue()
        ->and(TemplateVariableCatalog::has('lead.secret'))->toBeFalse()
        ->and(TemplateVariableCatalog::has('site_visit.upcoming.date'))->toBeTrue()
        ->and(TemplateVariableCatalog::token('lead.name'))->toBe('{{lead.name}}');
});
