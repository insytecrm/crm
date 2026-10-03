<?php

namespace App\Enums;

enum AutomationWorkflowFilter: string
{
    case All = 'all';
    case On = 'on';
    case Off = 'off';

    public function label(): string
    {
        return match ($this) {
            self::All => __('All'),
            self::On => __('On'),
            self::Off => __('Off'),
        };
    }

    public function emptyMessage(): string
    {
        return match ($this) {
            self::On => __('No workflows are turned on.'),
            self::Off => __('No workflows are turned off.'),
            self::All => __('No workflows yet.'),
        };
    }

    public static function fromRequest(?string $value): self
    {
        return self::tryFrom($value) ?? self::All;
    }
}
