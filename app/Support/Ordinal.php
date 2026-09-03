<?php

namespace App\Support;

class Ordinal
{
    public static function for(int $number): string
    {
        if ($number % 100 >= 11 && $number % 100 <= 13) {
            return $number.'th';
        }

        return $number.match ($number % 10) {
            1 => 'st',
            2 => 'nd',
            3 => 'rd',
            default => 'th',
        };
    }
}
