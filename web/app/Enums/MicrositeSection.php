<?php

namespace App\Enums;

enum MicrositeSection: string
{
    case Hero = 'hero';
    case Highlights = 'highlights';
    case About = 'about';
    case Configurations = 'configurations';
    case Amenities = 'amenities';
    case Visuals = 'visuals';
    case Location = 'location';
    case Developer = 'developer';
    case Cta = 'cta';

    public function label(): string
    {
        return match ($this) {
            self::Hero => __('Hero'),
            self::Highlights => __('Highlights'),
            self::About => __('About'),
            self::Configurations => __('Configurations'),
            self::Amenities => __('Amenities'),
            self::Visuals => __('Visuals'),
            self::Location => __('Location'),
            self::Developer => __('Developer'),
            self::Cta => __('Call to action'),
        };
    }

    /**
     * @return list<string>
     */
    public static function keys(): array
    {
        return array_map(fn (self $section): string => $section->value, self::cases());
    }
}
