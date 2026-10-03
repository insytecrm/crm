<?php

namespace App\Enums;

enum AiConversationStatus: string
{
    case Open = 'open';
    case Completed = 'completed';

    public function label(): string
    {
        return match ($this) {
            self::Open => __('Open'),
            self::Completed => __('Completed'),
        };
    }
}
