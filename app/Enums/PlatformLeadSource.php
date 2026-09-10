<?php

namespace App\Enums;

enum PlatformLeadSource: string
{
    case Website = 'website';
    case Meta = 'meta';
    case Google = 'google';
    case Referral = 'referral';
    case WhatsApp = 'whatsapp';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Website => __('Website'),
            self::Meta => __('Meta'),
            self::Google => __('Google'),
            self::Referral => __('Referral'),
            self::WhatsApp => __('WhatsApp'),
            self::Other => __('Other'),
        };
    }
}
