<?php

namespace App\Enums;

enum MessageTemplateFilter: string
{
    case All = 'all';
    case WhatsApp = 'whatsapp';
    case Email = 'email';
    case Sms = 'sms';

    public function label(): string
    {
        return match ($this) {
            self::All => __('All'),
            self::WhatsApp => __('WhatsApp'),
            self::Email => __('Email'),
            self::Sms => __('SMS'),
        };
    }

    public function emptyMessage(): string
    {
        return match ($this) {
            self::WhatsApp => __('No WhatsApp templates yet.'),
            self::Email => __('No email templates yet.'),
            self::Sms => __('No SMS templates yet.'),
            self::All => __('No templates yet.'),
        };
    }

    public function channel(): ?MessageTemplateChannel
    {
        return MessageTemplateChannel::tryFrom($this->value);
    }

    public static function fromRequest(?string $value): self
    {
        return self::tryFrom((string) $value) ?? self::All;
    }
}
