<?php

namespace App\Enums;

enum UtilityMailEncryption: string
{
    case Tls = 'tls';
    case Ssl = 'ssl';
    case None = 'none';

    public function label(): string
    {
        return match ($this) {
            self::Tls => __('TLS (Recommended)'),
            self::Ssl => __('SSL'),
            self::None => __('None'),
        };
    }

    /**
     * Laravel SMTP mailer scheme (Symfony DSN), not the encryption label shown in UI.
     *
     * @see config/mail.php Supported values: "smtp" (STARTTLS), "smtps" (implicit TLS), or null.
     */
    public function scheme(): ?string
    {
        return match ($this) {
            self::Tls => 'smtp',
            self::Ssl => 'smtps',
            self::None => null,
        };
    }
}
