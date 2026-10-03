<?php

namespace App\Support;

use App\Models\User;

class TenantHomeRedirect
{
    public static function routeFor(?User $user): string
    {
        if ($user !== null && $user->isAgentRole()) {
            return route('tenant.work.index', absolute: false);
        }

        return route('tenant.dashboard', absolute: false);
    }
}
