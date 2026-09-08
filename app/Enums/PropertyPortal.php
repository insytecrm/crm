<?php

namespace App\Enums;

enum PropertyPortal: string
{
    case NinetyNineAcres = '99acres';
    case Housing = 'housing';
    case MagicBricks = 'magicbricks';
    case NoBroker = 'nobroker';

    public function label(): string
    {
        return match ($this) {
            self::NinetyNineAcres => '99acres',
            self::Housing => 'Housing.com',
            self::MagicBricks => 'MagicBricks',
            self::NoBroker => 'NoBroker',
        };
    }

    public function description(): string
    {
        return __('Connect your :portal account to receive property inquiries into this workspace.', [
            'portal' => $this->label(),
        ]);
    }

    public function leadSource(): LeadSource
    {
        return match ($this) {
            self::NinetyNineAcres => LeadSource::NinetyNineAcres,
            self::Housing => LeadSource::Housing,
            self::MagicBricks => LeadSource::MagicBricks,
            self::NoBroker => LeadSource::NoBroker,
        };
    }

    /**
     * @return list<self>
     */
    public static function integrationCards(): array
    {
        return self::cases();
    }
}
