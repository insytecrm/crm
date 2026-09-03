<?php

namespace Database\Factories;

use App\Models\Lead;
use App\Models\LeadDocument;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LeadDocument>
 */
class LeadDocumentFactory extends Factory
{
    protected $model = LeadDocument::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'lead_id' => Lead::factory(),
            'uploaded_by_id' => User::query()->value('id'),
            'name' => fake()->word().'.pdf',
            'path' => 'leads/documents/'.fake()->uuid().'.pdf',
            'disk' => 'local',
            'mime_type' => 'application/pdf',
            'size' => fake()->numberBetween(1000, 500000),
        ];
    }
}
