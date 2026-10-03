<?php

namespace Database\Seeders;

use App\Actions\SeedDefaultPlans;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        app(SeedDefaultPlans::class)->handle();
    }
}
