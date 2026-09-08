<?php

namespace App\Support;

class AutomationRuntime
{
    private static bool $running = false;

    public static function isRunning(): bool
    {
        return self::$running;
    }

    /**
     * @template T
     *
     * @param  callable(): T  $callback
     * @return T
     */
    public static function run(callable $callback): mixed
    {
        $previous = self::$running;
        self::$running = true;

        try {
            return $callback();
        } finally {
            self::$running = $previous;
        }
    }
}
