<?php

namespace App\Enums;

use App\Enums\Concerns\ParsesFlexibleEnumValues;

enum PropertyType: string
{
    use ParsesFlexibleEnumValues;
    case Apartment = 'apartment';
    case Villa = 'villa';
    case Plot = 'plot';
    case Shop = 'shop';
    case Office = 'office';

    public function label(): string
    {
        return match ($this) {
            self::Apartment => __('Apartment'),
            self::Villa => __('Villa'),
            self::Plot => __('Plot'),
            self::Shop => __('Shop'),
            self::Office => __('Office'),
        };
    }
}
