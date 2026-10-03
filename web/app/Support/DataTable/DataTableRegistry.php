<?php

namespace App\Support\DataTable;

use App\Contracts\DataTableDefinition;
use App\Support\DataTable\Definitions\ActivitiesSiteVisitsTableDefinition;
use App\Support\DataTable\Definitions\ActivitiesTableDefinition;
use App\Support\DataTable\Definitions\BookingsTableDefinition;
use App\Support\DataTable\Definitions\FollowUpsTableDefinition;
use App\Support\DataTable\Definitions\InvoicesTableDefinition;
use App\Support\DataTable\Definitions\PayoutsTableDefinition;
use App\Support\DataTable\Definitions\SettingsRolesTableDefinition;
use App\Support\DataTable\Definitions\SettingsUsersTableDefinition;
use App\Support\DataTable\Definitions\SiteVisitsTableDefinition;
use App\Support\DataTable\Definitions\TasksTableDefinition;
use App\Support\DataTable\Definitions\TeamsTableDefinition;
use InvalidArgumentException;

class DataTableRegistry
{
    /**
     * @return list<string>
     */
    public static function keys(): array
    {
        return [
            'tasks',
            'bookings',
            'invoices',
            'payouts',
            'teams',
            'activities',
            'activities_site_visits',
            'follow_ups',
            'site_visits',
            'settings_users',
            'settings_roles',
        ];
    }

    public static function get(string $key): DataTableDefinition
    {
        return match ($key) {
            'tasks' => new TasksTableDefinition,
            'bookings' => new BookingsTableDefinition,
            'invoices' => new InvoicesTableDefinition,
            'payouts' => new PayoutsTableDefinition,
            'teams' => new TeamsTableDefinition,
            'activities' => new ActivitiesTableDefinition,
            'activities_site_visits' => new ActivitiesSiteVisitsTableDefinition,
            'follow_ups' => new FollowUpsTableDefinition,
            'site_visits' => new SiteVisitsTableDefinition,
            'settings_users' => new SettingsUsersTableDefinition,
            'settings_roles' => new SettingsRolesTableDefinition,
            default => throw new InvalidArgumentException("Unknown data table key [{$key}]."),
        };
    }
}
