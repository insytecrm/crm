<?php

namespace App\Enums;

use App\Enums\Concerns\ParsesFlexibleEnumValues;

enum MicrositeFont: string
{
    use ParsesFlexibleEnumValues;

    case Outfit = 'outfit';
    case Inter = 'inter';
    case Manrope = 'manrope';
    case DMSans = 'dm_sans';
    case CormorantGaramond = 'cormorant_garamond';
    case PlayfairDisplay = 'playfair_display';
    case LibreBaskerville = 'libre_baskerville';
    case Lora = 'lora';
    case SpaceGrotesk = 'space_grotesk';
    case InstrumentSerif = 'instrument_serif';

    public function label(): string
    {
        return match ($this) {
            self::Outfit => 'Outfit',
            self::Inter => 'Inter',
            self::Manrope => 'Manrope',
            self::DMSans => 'DM Sans',
            self::CormorantGaramond => 'Cormorant Garamond',
            self::PlayfairDisplay => 'Playfair Display',
            self::LibreBaskerville => 'Libre Baskerville',
            self::Lora => 'Lora',
            self::SpaceGrotesk => 'Space Grotesk',
            self::InstrumentSerif => 'Instrument Serif',
        };
    }

    public function cssFamily(): string
    {
        return match ($this) {
            self::Outfit => '"Outfit", ui-sans-serif, system-ui, sans-serif',
            self::Inter => '"Inter", ui-sans-serif, system-ui, sans-serif',
            self::Manrope => '"Manrope", ui-sans-serif, system-ui, sans-serif',
            self::DMSans => '"DM Sans", ui-sans-serif, system-ui, sans-serif',
            self::CormorantGaramond => '"Cormorant Garamond", Georgia, "Times New Roman", serif',
            self::PlayfairDisplay => '"Playfair Display", Georgia, "Times New Roman", serif',
            self::LibreBaskerville => '"Libre Baskerville", Georgia, "Times New Roman", serif',
            self::Lora => '"Lora", Georgia, "Times New Roman", serif',
            self::SpaceGrotesk => '"Space Grotesk", ui-sans-serif, system-ui, sans-serif',
            self::InstrumentSerif => '"Instrument Serif", Georgia, "Times New Roman", serif',
        };
    }

    public function bunnySlug(): string
    {
        return match ($this) {
            self::Outfit => 'outfit:400,500,600,700',
            self::Inter => 'inter:400,500,600,700',
            self::Manrope => 'manrope:400,500,600,700',
            self::DMSans => 'dm-sans:400,500,600,700',
            self::CormorantGaramond => 'cormorant-garamond:500,600,700',
            self::PlayfairDisplay => 'playfair-display:500,600,700',
            self::LibreBaskerville => 'libre-baskerville:400,700',
            self::Lora => 'lora:400,500,600,700',
            self::SpaceGrotesk => 'space-grotesk:400,500,600,700',
            self::InstrumentSerif => 'instrument-serif:400,400i',
        };
    }

    public function role(): string
    {
        return match ($this) {
            self::CormorantGaramond,
            self::PlayfairDisplay,
            self::LibreBaskerville,
            self::Lora,
            self::InstrumentSerif => 'display',
            default => 'body',
        };
    }

    /**
     * @return list<self>
     */
    public static function primaryOptions(): array
    {
        return array_values(array_filter(
            self::cases(),
            fn (self $font): bool => $font->role() === 'body',
        ));
    }

    /**
     * @return list<self>
     */
    public static function secondaryOptions(): array
    {
        return array_values(array_filter(
            self::cases(),
            fn (self $font): bool => $font->role() === 'display',
        ));
    }
}
