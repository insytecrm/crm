<?php

namespace App\Enums;

enum PlanPack: string
{
    case Off = 'off';
    case Basic = 'basic';
    case Advanced = 'advanced';
    case Custom = 'custom';

    public function label(): string
    {
        return match ($this) {
            self::Off => __('Off'),
            self::Basic => __('Basic'),
            self::Advanced => __('Advanced'),
            self::Custom => __('Custom'),
        };
    }
}
