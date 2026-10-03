<?php

namespace App\Enums;

enum MessageTemplateChannel: string
{
    case WhatsApp = 'whatsapp';
    case Email = 'email';
    case Sms = 'sms';

    public function label(): string
    {
        return match ($this) {
            self::WhatsApp => __('WhatsApp'),
            self::Email => __('Email'),
            self::Sms => __('SMS'),
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::WhatsApp => __('A WhatsApp message with merge variables.'),
            self::Email => __('An email with a subject line and merge variables.'),
            self::Sms => __('A short SMS with merge variables.'),
        };
    }

    public function requiresSubject(): bool
    {
        return $this === self::Email;
    }

    /**
     * @return array{badge: string, icon: string}
     */
    public function accent(): array
    {
        return match ($this) {
            self::WhatsApp => [
                'badge' => 'bg-emerald-50 text-emerald-700',
                'icon' => 'bg-emerald-100 text-emerald-600',
            ],
            self::Email => [
                'badge' => 'bg-sky-50 text-sky-700',
                'icon' => 'bg-sky-100 text-sky-600',
            ],
            self::Sms => [
                'badge' => 'bg-amber-50 text-amber-700',
                'icon' => 'bg-amber-100 text-amber-600',
            ],
        };
    }
}
