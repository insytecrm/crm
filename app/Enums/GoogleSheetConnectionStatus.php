<?php

namespace App\Enums;

enum GoogleSheetConnectionStatus: string
{
    case Draft = 'draft';
    case Verified = 'verified';
    case Connected = 'connected';
    case Paused = 'paused';

    public function label(): string
    {
        return match ($this) {
            self::Draft => __('Draft'),
            self::Verified => __('Verified'),
            self::Connected => __('Active'),
            self::Paused => __('Paused'),
        };
    }

    public function isSyncable(): bool
    {
        return $this === self::Connected;
    }
}
