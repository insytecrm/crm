<?php

namespace App\Enums;

enum ScheduledActivityOutcome: string
{
    case Connected = 'connected';
    case NoAnswer = 'no_answer';
    case Interested = 'interested';
    case NotInterested = 'not_interested';
    case CallbackRequested = 'callback_requested';
    case WrongNumber = 'wrong_number';

    /**
     * @return list<self>
     */
    public static function options(): array
    {
        return [
            self::Connected,
            self::NoAnswer,
            self::Interested,
            self::NotInterested,
            self::CallbackRequested,
            self::WrongNumber,
        ];
    }

    public function label(): string
    {
        return match ($this) {
            self::Connected => __('Connected'),
            self::NoAnswer => __('No answer'),
            self::Interested => __('Interested'),
            self::NotInterested => __('Not interested'),
            self::CallbackRequested => __('Callback requested'),
            self::WrongNumber => __('Wrong number'),
        };
    }
}
