<?php

namespace App\Enums;

enum DomainPurpose: string
{
    case Crm = 'crm';
    case Website = 'website';

    public function label(): string
    {
        return match ($this) {
            self::Crm => __('CRM domain'),
            self::Website => __('Website domain'),
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::Crm => __('Access your CRM from a custom domain such as crm.yourdomain.com.'),
            self::Website => __('Serve your public website and property microsites from a custom domain such as www.yourdomain.com.'),
        };
    }

    public function placeholder(): string
    {
        return match ($this) {
            self::Crm => 'crm.clientdomain.com',
            self::Website => 'www.clientdomain.com',
        };
    }
}
