<?php

namespace App\Enums;

enum AutomationRunStatus: string
{
    case Tested = 'tested';
    case Skipped = 'skipped';
    case Succeeded = 'succeeded';
    case Failed = 'failed';

    public function label(): string
    {
        return match ($this) {
            self::Tested => __('Tested'),
            self::Skipped => __('Skipped'),
            self::Succeeded => __('Succeeded'),
            self::Failed => __('Failed'),
        };
    }
}
