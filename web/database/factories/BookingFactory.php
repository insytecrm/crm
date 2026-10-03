<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\Lead;
use App\Models\Property;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Booking>
 */
class BookingFactory extends Factory
{
    protected $model = Booking::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $property = Property::factory()->create();
        $configurations = $property->configurations ?? [];
        $configurationIndex = 0;
        $configurationName = $configurations[0]['name'] ?? '2 BHK';

        return [
            'property_id' => $property->id,
            'lead_id' => Lead::factory(),
            'configuration_index' => $configurationIndex,
            'configuration_name' => $configurationName,
            'unit_number' => (string) fake()->numberBetween(101, 2504),
            'agreement_value' => fake()->numberBetween(5000000, 20000000),
            'booking_date' => fake()->dateTimeBetween('-1 month', 'now'),
            'created_by_id' => User::query()->value('id'),
        ];
    }
}
