<?php

namespace App\Contracts;

interface PlatformDashboardData
{
    /**
     * @return array{
     *     kpis: list<array{
     *         key: string,
     *         label: string,
     *         value: string,
     *         change: string,
     *         change_direction: 'up'|'down'|'flat',
     *         href: string
     *     }>,
     *     attention: list<array{
     *         key: string,
     *         severity: 'critical'|'warning'|'info',
     *         title: string,
     *         summary: string,
     *         meta: string|null,
     *         action_label: string,
     *         href: string
     *     }>,
     *     revenue: array{
     *         mrr: string,
     *         arr: string,
     *         arpu: string,
     *         href: string,
     *         months: list<array{label: string, revenue: int}>
     *     },
     *     partners: array{
     *         total: int,
     *         href: string,
     *         statuses: list<array{key: string, label: string, count: int, href: string}>
     *     },
     *     usage: array{
     *         total_active: int,
     *         change: string,
     *         change_direction: 'up'|'down'|'flat',
     *         href: string,
     *         integrations: list<array{name: string, count: int}>
     *     },
     *     activity: list<array{
     *         time: string,
     *         description: string,
     *         href: string|null
     *     }>,
     *     upcoming: array{
     *         today: list<array{label: string, href: string}>,
     *         this_week: list<array{label: string, href: string}>
     *     }
     * }
     */
    public function get(): array;
}
