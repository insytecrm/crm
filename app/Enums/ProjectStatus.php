<?php

namespace App\Enums;

use App\Enums\Concerns\ParsesFlexibleEnumValues;

enum ProjectStatus: string
{
    use ParsesFlexibleEnumValues;
    case NewLaunch = 'new_launch';
    case UnderConstruction = 'under_construction';
    case ReadyToMove = 'ready_to_move';

    public function label(): string
    {
        return match ($this) {
            self::NewLaunch => __('New Launch'),
            self::UnderConstruction => __('Under Construction'),
            self::ReadyToMove => __('Ready to Move'),
        };
    }
}
