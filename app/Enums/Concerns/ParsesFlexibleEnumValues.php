<?php

namespace App\Enums\Concerns;

trait ParsesFlexibleEnumValues
{
    public static function tryFromMixed(mixed $value): ?static
    {
        if ($value instanceof static) {
            return $value;
        }

        if (! is_string($value) || $value === '') {
            return null;
        }

        if ($resolved = static::tryFrom($value)) {
            return $resolved;
        }

        if ($resolved = static::tryFrom(strtolower($value))) {
            return $resolved;
        }

        foreach (static::cases() as $case) {
            if (strcasecmp($case->name, $value) === 0 || strcasecmp($case->label(), $value) === 0) {
                return $case;
            }
        }

        return null;
    }
}
