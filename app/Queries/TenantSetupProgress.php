<?php

namespace App\Queries;

use App\Models\FacebookPageConnection;
use App\Models\GoogleSheetConnection;
use App\Models\Lead;
use App\Models\LeadRoutingRule;
use App\Models\Property;
use App\Models\User;
use Illuminate\Support\Facades\Schema;

class TenantSetupProgress
{
    /**
     * @return array{
     *     completed: int,
     *     total: int,
     *     dismissed: bool,
     *     items: list<array{
     *         key: string,
     *         label: string,
     *         done: bool,
     *         href: ?string,
     *     }>,
     * }
     */
    public function forUser(User $user): array
    {
        $items = [
            $this->item(
                'property',
                __('Add a property'),
                Property::query()->exists(),
                route('tenant.properties.create'),
            ),
            $this->item(
                'lead',
                __('Create your first lead'),
                Lead::query()->exists(),
                route('tenant.leads.index', ['add' => 1]),
            ),
            $this->item(
                'team',
                __('Invite a team member'),
                User::query()->count() > 1,
                route('tenant.settings.index', ['tab' => 'users']),
            ),
            $this->item(
                'routing',
                __('Set up lead routing'),
                LeadRoutingRule::query()->exists(),
                route('tenant.teams.index'),
            ),
            $this->item(
                'integration',
                __('Connect a lead source'),
                $this->hasLeadSourceConnected(),
                route('tenant.settings.index', ['tab' => 'integrations']),
            ),
        ];

        $completed = collect($items)->where('done', true)->count();

        return [
            'completed' => $completed,
            'total' => count($items),
            'dismissed' => (bool) ($user->preferences['setup_progress_dismissed'] ?? false),
            'items' => $items,
        ];
    }

    /**
     * @return array{key: string, label: string, done: bool, href: ?string}
     */
    private function item(string $key, string $label, bool $done, ?string $href): array
    {
        return [
            'key' => $key,
            'label' => $label,
            'done' => $done,
            'href' => $href,
        ];
    }

    private function hasLeadSourceConnected(): bool
    {
        $tenant = tenant();

        if ($tenant === null) {
            return false;
        }

        if (filled($tenant->lead_api_token)) {
            return true;
        }

        if (Schema::hasTable('google_sheet_connections') && GoogleSheetConnection::query()->exists()) {
            return true;
        }

        if (Schema::hasTable('facebook_page_connections') && FacebookPageConnection::query()->exists()) {
            return true;
        }

        return false;
    }
}
