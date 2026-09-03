<?php

namespace Database\Factories;

use App\Enums\ProjectStatus;
use App\Enums\PropertyType;
use App\Models\Property;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Property>
 */
class PropertyFactory extends Factory
{
    protected $model = Property::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $userId = User::query()->value('id');
        $possessionMonth = fake()->numberBetween(1, 12);
        $possessionYear = fake()->numberBetween(now()->year, now()->year + 5);

        return [
            'developer_name' => fake()->company(),
            'project_name' => fake()->words(3, true),
            'project_location' => fake()->city(),
            'rera_number' => strtoupper(fake()->bothify('??#####')),
            'property_type' => fake()->randomElement(PropertyType::cases()),
            'project_status' => fake()->randomElement(ProjectStatus::cases()),
            'possession_date' => sprintf('%02d-%d', $possessionMonth, $possessionYear),
            'total_land_parcel_acres' => fake()->randomFloat(2, 1, 50),
            'total_towers' => fake()->numberBetween(1, 8),
            'total_floors' => fake()->randomElement(['G+B+22', 'G+15', 'B+20+Terrace']),
            'carpet_area_from_sqft' => fake()->numberBetween(600, 1200),
            'carpet_area_to_sqft' => fake()->numberBetween(1300, 2500),
            'price_from' => fake()->numberBetween(3000000, 8000000),
            'price_to' => fake()->numberBetween(9000000, 25000000),
            'tagging_period_days' => fake()->randomElement([45, 60, 90, 120]),
            'payout_percent' => fake()->randomFloat(2, 1, 5),
            'sourcing_manager_name' => fake()->name(),
            'sourcing_manager_contact' => fake()->numerify('+91 9#########'),
            'amenities' => fake()->randomElements([
                'Swimming Pool',
                'Gym',
                'Clubhouse',
                'Jogging Track',
                'Kids Play Area',
                'Landscaped Garden',
            ], fake()->numberBetween(2, 4)),
            'configurations' => [
                [
                    'name' => '2 BHK',
                    'carpet_area_sqft' => fake()->numberBetween(650, 950),
                    'price' => fake()->numberBetween(7500000, 11000000),
                    'unit_count' => fake()->numberBetween(40, 180),
                ],
                [
                    'name' => '3 BHK',
                    'carpet_area_sqft' => fake()->numberBetween(1000, 1400),
                    'price' => fake()->numberBetween(11000000, 18000000),
                    'unit_count' => fake()->numberBetween(20, 120),
                ],
            ],
            'created_by_id' => $userId,
        ];
    }
}
