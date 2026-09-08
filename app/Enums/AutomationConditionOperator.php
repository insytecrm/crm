<?php

namespace App\Enums;

enum AutomationConditionOperator: string
{
    case Equals = 'equals';
    case NotEquals = 'not_equals';
    case In = 'in';
    case Contains = 'contains';
    case IsEmpty = 'is_empty';
    case ChangedTo = 'changed_to';

    public function label(): string
    {
        return match ($this) {
            self::Equals => __('equals'),
            self::NotEquals => __('does not equal'),
            self::In => __('is any of'),
            self::Contains => __('contains'),
            self::IsEmpty => __('is empty'),
            self::ChangedTo => __('changed to'),
        };
    }
}
