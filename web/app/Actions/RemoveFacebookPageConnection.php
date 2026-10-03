<?php

namespace App\Actions;

use App\Models\FacebookPageConnection;
use App\Models\FacebookPageRegistration;

class RemoveFacebookPageConnection
{
    public function handle(FacebookPageConnection $connection): void
    {
        FacebookPageRegistration::query()
            ->where('page_id', $connection->page_id)
            ->delete();

        $connection->delete();
    }
}
