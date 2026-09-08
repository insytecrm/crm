<?php

namespace Database\Factories;

use App\Enums\MicrositeFont;
use App\Enums\MicrositeTheme;
use App\Models\Property;
use App\Models\PropertyMicrosite;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PropertyMicrosite>
 */
class PropertyMicrositeFactory extends Factory
{
    protected $model = PropertyMicrosite::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'property_id' => Property::factory(),
            'theme_preset' => MicrositeTheme::Noir,
            'theme_accent' => null,
            'font_primary' => MicrositeFont::Outfit,
            'font_secondary' => MicrositeFont::CormorantGaramond,
            'phone' => null,
            'whatsapp' => null,
            'cta_label' => null,
            'lead_form' => [],
            'content' => [],
            'media' => [],
        ];
    }
}
