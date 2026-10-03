<?php

namespace App\Enums;

enum ScheduledActivityContactMethod: string
{
    case Call = 'call';
    case WhatsApp = 'whatsapp';
    case Email = 'email';
    case Meeting = 'meeting';

    /**
     * @return list<self>
     */
    public static function options(): array
    {
        return [
            self::Call,
            self::WhatsApp,
            self::Email,
            self::Meeting,
        ];
    }

    public function label(): string
    {
        return match ($this) {
            self::Call => __('Call'),
            self::WhatsApp => __('WhatsApp'),
            self::Email => __('Email'),
            self::Meeting => __('Meeting'),
        };
    }
}
