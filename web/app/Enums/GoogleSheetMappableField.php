<?php

namespace App\Enums;

enum GoogleSheetMappableField: string
{
    case Name = 'name';
    case Phone = 'phone';
    case Email = 'email';
    case Source = 'source';
    case Budget = 'budget';
    case Location = 'location';
    case PropertyType = 'property_type';
    case Configuration = 'configuration';

    public function label(): string
    {
        return match ($this) {
            self::Name => __('Name'),
            self::Phone => __('Phone'),
            self::Email => __('Email'),
            self::Source => __('Sub-source'),
            self::Budget => __('Budget'),
            self::Location => __('Location'),
            self::PropertyType => __('Property type'),
            self::Configuration => __('Configuration'),
        };
    }

    public function isRequired(): bool
    {
        return $this === self::Name;
    }
}
