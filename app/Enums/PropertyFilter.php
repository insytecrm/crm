<?php

namespace App\Enums;

enum PropertyFilter: string
{
    case All = 'all';
    case Active = 'active';
    case Inactive = 'inactive';
    case Featured = 'featured';

    public function label(): string
    {
        return match ($this) {
            self::All => __('Total'),
            self::Active => __('Active'),
            self::Inactive => __('Deactive'),
            self::Featured => __('Featured'),
        };
    }

    public function emptyMessage(): string
    {
        return match ($this) {
            self::Active => __('No active properties.'),
            self::Inactive => __('No deactive properties.'),
            self::Featured => __('No featured properties.'),
            self::All => __('No properties yet.'),
        };
    }

    public static function fromRequest(?string $value): self
    {
        return self::tryFrom($value) ?? self::All;
    }
}
