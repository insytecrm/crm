<?php

namespace App\Support;

class RenderMessageTemplate
{
    /**
     * @param  array<string, mixed>  $values
     */
    public function handle(string $text, array $values): string
    {
        return (string) preg_replace_callback(
            '/\{\{([a-z0-9_.]+)\}\}/',
            function (array $matches) use ($values): string {
                $key = $matches[1];

                if (! array_key_exists($key, $values)) {
                    return $matches[0];
                }

                $value = $values[$key];

                if ($value === null) {
                    return '';
                }

                return is_scalar($value) ? (string) $value : $matches[0];
            },
            $text,
        );
    }
}
