<?php

namespace App\Support\Microsite;

use Illuminate\Support\Str;

class MicrositeEmbed
{
    public static function video(string $url): ?string
    {
        $url = trim($url);

        if ($url === '' || filter_var($url, FILTER_VALIDATE_URL) === false) {
            return null;
        }

        $host = Str::lower((string) parse_url($url, PHP_URL_HOST));
        $path = (string) parse_url($url, PHP_URL_PATH);
        $query = [];
        parse_str((string) parse_url($url, PHP_URL_QUERY), $query);

        if (str_contains($host, 'youtu.be')) {
            $id = trim($path, '/');

            return $id !== '' ? 'https://www.youtube.com/embed/'.rawurlencode($id) : null;
        }

        if (str_contains($host, 'youtube.com')) {
            if (filled($query['v'] ?? null)) {
                return 'https://www.youtube.com/embed/'.rawurlencode((string) $query['v']);
            }

            if (str_contains($path, '/embed/')) {
                $id = trim(Str::after($path, '/embed/'), '/');

                return $id !== '' ? 'https://www.youtube.com/embed/'.rawurlencode($id) : null;
            }

            if (str_contains($path, '/shorts/')) {
                $id = trim(Str::after($path, '/shorts/'), '/');

                return $id !== '' ? 'https://www.youtube.com/embed/'.rawurlencode($id) : null;
            }
        }

        if (str_contains($host, 'vimeo.com')) {
            $id = trim($path, '/');

            if (ctype_digit($id)) {
                return 'https://player.vimeo.com/video/'.$id;
            }
        }

        return null;
    }

    public static function map(?string $mapUrl, ?string $location): ?string
    {
        if (filled($mapUrl)) {
            $url = trim($mapUrl);

            if (filter_var($url, FILTER_VALIDATE_URL) === false) {
                return filled($location) ? self::searchMap((string) $location) : null;
            }

            $host = Str::lower((string) parse_url($url, PHP_URL_HOST));

            if (! str_contains($host, 'google.com') && ! str_contains($host, 'goo.gl') && ! str_contains($host, 'google.co')) {
                return filled($location) ? self::searchMap((string) $location) : null;
            }

            if (str_contains($url, '/maps/embed') || str_contains($url, 'output=embed')) {
                return $url;
            }

            return self::searchMap($url);
        }

        return filled($location) ? self::searchMap((string) $location) : null;
    }

    private static function searchMap(string $query): string
    {
        return 'https://maps.google.com/maps?q='.rawurlencode($query).'&z=14&output=embed';
    }
}
