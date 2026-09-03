<?php

namespace App\Enums;

enum SiteVisitType: string
{
    case FreshVisit = 'fresh_visit';
    case Revisit = 'revisit';

    /**
     * @return list<self>
     */
    public static function options(): array
    {
        return [
            self::FreshVisit,
            self::Revisit,
        ];
    }

    public function label(): string
    {
        return match ($this) {
            self::FreshVisit => __('Fresh Visit'),
            self::Revisit => __('Revisit'),
        };
    }
}
