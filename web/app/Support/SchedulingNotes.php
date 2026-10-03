<?php

namespace App\Support;

class SchedulingNotes
{
    public static function fromDescription(string $description): ?string
    {
        $parts = explode(' — ', $description, 2);

        if (! isset($parts[1])) {
            return null;
        }

        $notes = trim($parts[1]);

        return $notes !== '' ? $notes : null;
    }
}
