<?php

namespace App\Enums;

use App\Enums\Concerns\ParsesFlexibleEnumValues;

enum LeadSource: string
{
    use ParsesFlexibleEnumValues;

    case Referral = 'referral';
    case WalkIn = 'walk_in';
    case Facebook = 'facebook';
    case GoogleSheets = 'google_sheets';
    case Api = 'api';
    case NinetyNineAcres = '99acres';
    case Housing = 'housing';
    case MagicBricks = 'magicbricks';
    case NoBroker = 'nobroker';
    case Microsite = 'microsite';
    case Automation = 'automation';
    case InsyteAi = 'insyte_ai';

    public function label(): string
    {
        return match ($this) {
            self::Referral => __('Referral'),
            self::WalkIn => __('Walk-in'),
            self::Facebook => __('Facebook Lead Ads'),
            self::GoogleSheets => __('Google Sheets'),
            self::Api => __('Lead API'),
            self::NinetyNineAcres => '99acres',
            self::Housing => 'Housing.com',
            self::MagicBricks => 'MagicBricks',
            self::NoBroker => 'NoBroker',
            self::Microsite => __('Microsite'),
            self::Automation => __('Automation'),
            self::InsyteAi => __('Insyte AI'),
        };
    }

    public function isManual(): bool
    {
        return in_array($this, self::manualSelectableCases(), true);
    }

    /**
     * @return list<self>
     */
    public static function manualSelectableCases(): array
    {
        return [
            self::Referral,
            self::WalkIn,
        ];
    }

    /**
     * @return list<self>
     */
    public static function filterCases(): array
    {
        return self::cases();
    }
}
