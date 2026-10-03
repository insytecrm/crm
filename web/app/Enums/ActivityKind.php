<?php

namespace App\Enums;

enum ActivityKind: string
{
    case FollowUp = 'follow_up';
    case SiteVisit = 'site_visit';

    public static function fromRequest(?string $value): ?self
    {
        if ($value === null || $value === '') {
            return null;
        }

        return self::tryFrom($value);
    }
}
