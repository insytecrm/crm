<?php

namespace App\Enums;

enum LeadRoutingDistribution: string
{
    case Equal = 'equal';
    case RoundRobin = 'round_robin';
    case Custom = 'custom';

    public function label(): string
    {
        return match ($this->value) {
            'equal' => __('Equal'),
            'round_robin' => __('Round robin'),
            'custom' => __('Custom weights'),
        };
    }

    public function description(): string
    {
        return match ($this->value) {
            'equal' => __('Share leads evenly by who has the fewest open leads.'),
            'round_robin' => __('Give the next lead to the next teammate in turn.'),
            'custom' => __('Send a set number of leads to each teammate (for example 2, 2, 1).'),
        };
    }
}
