<?php

namespace App\Actions;

use App\Models\Plan;

class UpdatePlan
{
    public function __construct(private CreatePlan $createPlan) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(Plan $plan, array $data): Plan
    {
        $plan->update($this->createPlan->attributes($data, $plan->key));

        return $plan->refresh();
    }
}
