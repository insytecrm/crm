<?php

namespace App\Events;

use App\Models\LeadActivity;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LeadActivityRecorded implements ShouldDispatchAfterCommit
{
    use Dispatchable, SerializesModels;

    public function __construct(public LeadActivity $activity) {}
}
