<?php

namespace App\Enums;

use App\Enums\Concerns\ParsesFlexibleEnumValues;

enum MicrositeTheme: string
{
    use ParsesFlexibleEnumValues;

    case Noir = 'noir';
    case Ivory = 'ivory';
    case Forest = 'forest';
    case Sand = 'sand';

    public function label(): string
    {
        return match ($this) {
            self::Noir => __('Noir'),
            self::Ivory => __('Ivory'),
            self::Forest => __('Forest'),
            self::Sand => __('Sand'),
        };
    }

    /**
     * @return array{bg: string, surface: string, text: string, muted: string, border: string, accent: string, accent_text: string, hero: string}
     */
    public function tokens(): array
    {
        return match ($this) {
            self::Noir => [
                'bg' => '#0b0a09',
                'surface' => '#151311',
                'text' => '#f4efe6',
                'muted' => '#b3a89a',
                'border' => 'rgba(244, 239, 230, 0.12)',
                'accent' => '#c4a574',
                'accent_text' => '#0b0a09',
                'hero' => 'dark',
            ],
            self::Ivory => [
                'bg' => '#f6f1e8',
                'surface' => '#ffffff',
                'text' => '#1c1915',
                'muted' => '#6f675c',
                'border' => 'rgba(28, 25, 21, 0.10)',
                'accent' => '#8a6a3b',
                'accent_text' => '#f6f1e8',
                'hero' => 'light',
            ],
            self::Forest => [
                'bg' => '#0e1612',
                'surface' => '#15201a',
                'text' => '#eef4ee',
                'muted' => '#9bb0a3',
                'border' => 'rgba(238, 244, 238, 0.12)',
                'accent' => '#c5a059',
                'accent_text' => '#0e1612',
                'hero' => 'dark',
            ],
            self::Sand => [
                'bg' => '#1b1713',
                'surface' => '#241f19',
                'text' => '#f3eadc',
                'muted' => '#b9ad9a',
                'border' => 'rgba(243, 234, 220, 0.12)',
                'accent' => '#d9b88a',
                'accent_text' => '#1b1713',
                'hero' => 'dark',
            ],
        };
    }
}
