<?php

namespace App\Contracts;

use App\Models\Tenant;
use Illuminate\Http\Request;

interface ChannelPartnerProfileData
{
    /**
     * Shared partner chrome for every tab.
     *
     * @return array{
     *     name: string,
     *     status: string,
     *     status_label: string,
     *     plan_key: string|null,
     *     plan_label: string,
     *     owner_name: string|null,
     *     joined_label: string,
     *     joined_at: string|null,
     *     workspace_url: string,
     *     tabs: list<array{key: string, label: string, href: string, active: bool}>,
     *     more_actions: list<array{label: string, href: string|null, method: string|null, danger: bool, disabled: bool}>
     * }
     */
    public function shell(Tenant $tenant, string $activeTab): array;

    /**
     * @return array{
     *     account: list<array{label: string, value: string}>,
     *     usage: list<array{label: string, used_label: string, limit_label: string|null, percent: float|null}>,
     *     integrations: list<array{key: string, label: string, status: string, status_label: string}>,
     *     recent_activity: list<array{time: string, description: string, href: string|null}>,
     *     attention: list<array{severity: 'critical'|'warning'|'info', message: string}>
     * }
     */
    public function overview(Tenant $tenant): array;

    /**
     * @return array{
     *     total: int,
     *     filters: array{search: string, role: string|null, status: string|null},
     *     role_options: list<array{value: string, label: string}>,
     *     status_options: list<array{value: string, label: string}>,
     *     edit_roles: list<array{id: int, name: string}>,
     *     users: list<array{
     *         id: int|string,
     *         name: string,
     *         email: string,
     *         role: string,
     *         role_id: int|null,
     *         status: string,
     *         status_label: string,
     *         last_active_label: string,
     *         created_label: string,
     *         actions: list<array{
     *             label: string,
     *             href: string|null,
     *             method: string|null,
     *             modal: string|null,
     *             disabled: bool,
     *             confirm: string|null,
     *             danger: bool,
     *             payload?: array<string, string>
     *         }>
     *     }>
     * }
     */
    public function users(Tenant $tenant, Request $request): array;

    /**
     * @return array{
     *     plan_label: string,
     *     price_label: string,
     *     status: string,
     *     status_label: string,
     *     details: list<array{label: string, value: string}>,
     *     actions: list<array{label: string, href: string|null, variant: string, disabled: bool}>,
     *     invoices: list<array{date: string, invoice: string, amount: string, status: string, status_label: string}>
     * }
     */
    public function subscription(Tenant $tenant): array;

    /**
     * @return array{
     *     meters: list<array{
     *         key: string,
     *         label: string,
     *         used: float|int,
     *         limit: float|int|null,
     *         used_label: string,
     *         limit_label: string|null,
     *         unit: string|null,
     *         percent: float|null
     *     }>,
     *     month_activity: list<array{label: string, value: string}>,
     *     warnings: list<array{message: string}>
     * }
     */
    public function usage(Tenant $tenant): array;

    /**
     * @return array{
     *     connected_count: int,
     *     attention_count: int,
     *     items: list<array{
     *         key: string,
     *         label: string,
     *         status: string,
     *         status_label: string,
     *         last_sync_label: string|null,
     *         attention_message: string|null,
     *         actions: list<array{label: string, href: string|null, disabled: bool}>
     *     }>
     * }
     */
    public function integrations(Tenant $tenant): array;

    /**
     * @return array{
     *     filters: list<array{key: string, label: string, href: string, active: bool}>,
     *     groups: list<array{
     *         label: string,
     *         events: list<array{time: string, description: string, category: string}>
     *     }>
     * }
     */
    public function activity(Tenant $tenant, Request $request): array;
}
