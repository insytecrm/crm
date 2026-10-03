<?php

use App\Support\Microsite\GroupedPropertyConfigurations;
use App\Support\Microsite\MicrositeMoney;
use Tests\TestCase;

uses(TestCase::class);

test('configurations with the same name are grouped into one card', function () {
    $groups = (new GroupedPropertyConfigurations)->group([
        ['name' => '2 BHK', 'carpet_area_sqft' => 700, 'price' => 8_000_000, 'unit_count' => 10],
        ['name' => '2 BHK', 'carpet_area_sqft' => 900, 'price' => 11_000_000, 'unit_count' => 4],
        ['name' => '3 BHK', 'carpet_area_sqft' => 1200, 'price' => 15_000_000, 'unit_count' => 6],
    ]);

    expect($groups)->toHaveCount(2)
        ->and($groups[0]['name'])->toBe('2 BHK')
        ->and($groups[0]['variant_count'])->toBe(2)
        ->and($groups[0]['carpet_from'])->toBe(700)
        ->and($groups[0]['carpet_to'])->toBe(900)
        ->and($groups[1]['name'])->toBe('3 BHK')
        ->and($groups[1]['variant_count'])->toBe(1);
});

test('indian rupee amounts use lakh and crore labels', function () {
    expect(MicrositeMoney::rupees(8_000_000))->toBe('₹80 L')
        ->and(MicrositeMoney::rupees(15_000_000))->toBe('₹1.5 Cr')
        ->and(MicrositeMoney::range(8_000_000, 11_000_000))->toBe('₹80 L – ₹1.1 Cr');
});
