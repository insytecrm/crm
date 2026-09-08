<?php

namespace Database\Factories;

use App\Enums\LeadRoutingDistribution;
use App\Enums\LeadSource;
use App\Models\LeadRoutingRule;
use App\Models\SalesTeam;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LeadRoutingRule>
 */
class LeadRoutingRuleFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'source' => LeadSource::Facebook->value,
            'sub_source' => null,
            'sales_team_id' => SalesTeam::factory(),
            'distribution' => LeadRoutingDistribution::RoundRobin,
            'is_active' => true,
            'distribution_cursor' => 0,
            'created_by_id' => User::factory(),
        ];
    }
}
