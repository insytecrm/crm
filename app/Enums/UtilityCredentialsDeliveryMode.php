<?php

namespace App\Enums;

enum UtilityCredentialsDeliveryMode: string
{
    case Always = 'always';
    case Ask = 'ask';

    public function label(): string
    {
        return match ($this) {
            self::Always => __('Always send automatically'),
            self::Ask => __('Ask me each time'),
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::Always => __('Credentials and notices are emailed whenever they are created, if SMTP is configured.'),
            self::Ask => __('Show a checkbox when creating users or onboarding so you can choose per action.'),
        };
    }
}
