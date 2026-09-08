<?php

namespace App\Support\Platform;

use App\Enums\FacebookPageConnectionStatus;
use App\Enums\GoogleSheetConnectionStatus;
use App\Enums\LeadActivityType;
use App\Enums\LeadScheduledEventType;
use App\Enums\PropertyPortal;
use App\Models\AiMessage;
use App\Models\Automation;
use App\Models\AutomationRun;
use App\Models\FacebookPageConnection;
use App\Models\GoogleSheetConnection;
use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\LeadScheduledEvent;
use App\Models\PortalWebhookEndpoint;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Carbon;
use Throwable;

/**
 * Reads live workspace metrics for Super Admin partner screens.
 * Billing/plan limits stay outside this class.
 */
class ChannelPartnerTenantSnapshot
{
    /**
     * @return array{
     *     users_active: int,
     *     users_active_this_month: int,
     *     leads_total: int,
     *     leads_this_month: int,
     *     automations_total: int,
     *     automation_runs_this_month: int,
     *     site_visits_this_month: int,
     *     messages_this_month: int,
     *     integrations: list<array{
     *         key: string,
     *         label: string,
     *         status: string,
     *         status_label: string,
     *         last_sync_label: string|null,
     *         attention_message: string|null,
     *         available: bool,
     *         actions: list<array{label: string, href: string|null, disabled: bool}>
     *     }>,
     *     activity_events: list<array{group_label: string, time: string, description: string, category: string, sort: int}>
     * }
     */
    public function for(Tenant $tenant): array
    {
        $empty = $this->emptySnapshot();

        try {
            $tenantMetrics = $tenant->run(function (): array {
                $monthStart = now()->startOfMonth();

                return [
                    'users_active' => User::query()->where('is_active', true)->count(),
                    'users_active_this_month' => User::query()
                        ->where('is_active', true)
                        ->where('updated_at', '>=', $monthStart)
                        ->count(),
                    'leads_total' => Lead::query()->count(),
                    'leads_this_month' => Lead::query()->where('created_at', '>=', $monthStart)->count(),
                    'automations_total' => Automation::query()->count(),
                    'automation_runs_this_month' => AutomationRun::query()->where('created_at', '>=', $monthStart)->count(),
                    'site_visits_this_month' => LeadScheduledEvent::query()
                        ->where('type', LeadScheduledEventType::SiteVisit)
                        ->where('created_at', '>=', $monthStart)
                        ->count(),
                    'messages_this_month' => LeadActivity::query()
                        ->where('type', LeadActivityType::WhatsAppMessage)
                        ->where('created_at', '>=', $monthStart)
                        ->count(),
                    'ai_messages_this_month' => AiMessage::query()
                        ->where('role', 'user')
                        ->where('created_at', '>=', $monthStart)
                        ->count(),
                    'google_sheets' => $this->googleSheetsState(),
                    'facebook' => $this->facebookState(),
                    'activity_events' => $this->tenantActivityEvents(),
                ];
            });
        } catch (Throwable) {
            return array_merge($empty, [
                'integrations' => $this->buildIntegrations($tenant, null, null),
            ]);
        }

        return [
            'users_active' => $tenantMetrics['users_active'],
            'users_active_this_month' => $tenantMetrics['users_active_this_month'],
            'leads_total' => $tenantMetrics['leads_total'],
            'leads_this_month' => $tenantMetrics['leads_this_month'],
            'automations_total' => $tenantMetrics['automations_total'],
            'automation_runs_this_month' => $tenantMetrics['automation_runs_this_month'],
            'site_visits_this_month' => $tenantMetrics['site_visits_this_month'],
            'messages_this_month' => $tenantMetrics['messages_this_month'],
            'ai_messages_this_month' => $tenantMetrics['ai_messages_this_month'],
            'integrations' => $this->buildIntegrations(
                $tenant,
                $tenantMetrics['google_sheets'],
                $tenantMetrics['facebook'],
            ),
            'activity_events' => $tenantMetrics['activity_events'],
        ];
    }

    /**
     * @return array{
     *     users_active: int,
     *     users_active_this_month: int,
     *     leads_total: int,
     *     leads_this_month: int,
     *     automations_total: int,
     *     automation_runs_this_month: int,
     *     site_visits_this_month: int,
     *     messages_this_month: int,
     *     integrations: list<array{
     *         key: string,
     *         label: string,
     *         status: string,
     *         status_label: string,
     *         last_sync_label: string|null,
     *         attention_message: string|null,
     *         available: bool,
     *         actions: list<array{label: string, href: string|null, disabled: bool}>
     *     }>,
     *     activity_events: list<array{group_label: string, time: string, description: string, category: string, sort: int}>
     * }
     */
    private function emptySnapshot(): array
    {
        return [
            'users_active' => 0,
            'users_active_this_month' => 0,
            'leads_total' => 0,
            'leads_this_month' => 0,
            'automations_total' => 0,
            'automation_runs_this_month' => 0,
            'site_visits_this_month' => 0,
            'messages_this_month' => 0,
            'ai_messages_this_month' => 0,
            'integrations' => [],
            'activity_events' => [],
        ];
    }

    /**
     * @param  array{status: string, status_label: string, last_sync_label: string|null, attention_message: string|null}|null  $googleSheets
     * @param  array{status: string, status_label: string, last_sync_label: string|null, attention_message: string|null}|null  $facebook
     * @return list<array{
     *     key: string,
     *     label: string,
     *     status: string,
     *     status_label: string,
     *     last_sync_label: string|null,
     *     attention_message: string|null,
     *     available: bool,
     *     actions: list<array{label: string, href: string|null, disabled: bool}>
     * }>
     */
    private function buildIntegrations(Tenant $tenant, ?array $googleSheets, ?array $facebook): array
    {
        $items = [
            $this->item('api', __('Lead API'), $this->leadApiState($tenant)),
            $this->item('google_sheets', __('Google Sheets'), $googleSheets ?? $this->notConnectedState()),
            $this->item('facebook', __('Facebook Lead Ads'), $facebook ?? $this->notConnectedState()),
        ];

        foreach (PropertyPortal::cases() as $portal) {
            $items[] = $this->item($portal->value, $portal->label(), $this->portalState($tenant, $portal));
        }

        foreach ([
            ['key' => 'whatsapp', 'label' => __('WhatsApp')],
            ['key' => 'email', 'label' => __('Email')],
            ['key' => 'calendar', 'label' => __('Calendar')],
        ] as $comingSoon) {
            $items[] = $this->item($comingSoon['key'], $comingSoon['label'], [
                'status' => 'coming_soon',
                'status_label' => __('Coming soon'),
                'last_sync_label' => null,
                'attention_message' => null,
            ], available: false);
        }

        return $items;
    }

    /**
     * @param  array{status: string, status_label: string, last_sync_label: string|null, attention_message: string|null}  $state
     * @return array{
     *     key: string,
     *     label: string,
     *     status: string,
     *     status_label: string,
     *     last_sync_label: string|null,
     *     attention_message: string|null,
     *     available: bool,
     *     actions: list<array{label: string, href: string|null, disabled: bool}>
     * }
     */
    private function item(string $key, string $label, array $state, bool $available = true): array
    {
        return [
            'key' => $key,
            'label' => $label,
            'status' => $state['status'],
            'status_label' => $state['status_label'],
            'last_sync_label' => $state['last_sync_label'],
            'attention_message' => $state['attention_message'],
            'available' => $available,
            'actions' => [
                ['label' => __('View logs'), 'href' => null, 'disabled' => true],
            ],
        ];
    }

    /**
     * @return array{status: string, status_label: string, last_sync_label: string|null, attention_message: string|null}
     */
    private function leadApiState(Tenant $tenant): array
    {
        // Visiting Settings can issue a token; only treat as connected after it has been used.
        if (! filled($tenant->lead_api_token_hash) || $tenant->lead_api_token_last_used_at === null) {
            return $this->notConnectedState();
        }

        return [
            'status' => 'connected',
            'status_label' => __('Connected'),
            'last_sync_label' => $tenant->lead_api_token_last_used_at->diffForHumans(),
            'attention_message' => null,
        ];
    }

    /**
     * @return array{status: string, status_label: string, last_sync_label: string|null, attention_message: string|null}
     */
    private function portalState(Tenant $tenant, PropertyPortal $portal): array
    {
        /** @var PortalWebhookEndpoint|null $endpoint */
        $endpoint = $tenant->portalWebhookEndpoints()
            ->where('portal', $portal->value)
            ->first();

        // Opening a portal settings page auto-creates a webhook row — that is not a connection.
        // Connected only when the partner has actually received traffic on that webhook.
        if ($endpoint === null || $endpoint->last_used_at === null) {
            return $this->notConnectedState();
        }

        if (! $endpoint->is_active) {
            return [
                'status' => 'needs_attention',
                'status_label' => __('Needs Attention'),
                'last_sync_label' => $endpoint->last_used_at->diffForHumans(),
                'attention_message' => __('Webhook is inactive'),
            ];
        }

        return [
            'status' => 'connected',
            'status_label' => __('Connected'),
            'last_sync_label' => $endpoint->last_used_at->diffForHumans(),
            'attention_message' => null,
        ];
    }

    /**
     * @return array{status: string, status_label: string, last_sync_label: string|null, attention_message: string|null}
     */
    private function googleSheetsState(): array
    {
        $connections = GoogleSheetConnection::query()->get();

        if ($connections->isEmpty()) {
            return $this->notConnectedState();
        }

        $connected = $connections->first(
            fn (GoogleSheetConnection $connection): bool => $connection->status === GoogleSheetConnectionStatus::Connected
        );
        $paused = $connections->first(
            fn (GoogleSheetConnection $connection): bool => $connection->status === GoogleSheetConnectionStatus::Paused
        );
        $withError = $connections->first(
            fn (GoogleSheetConnection $connection): bool => filled($connection->last_error)
        );

        $latestSync = $connections
            ->pluck('last_synced_at')
            ->filter()
            ->sortDesc()
            ->first();

        if ($withError instanceof GoogleSheetConnection && ($connected || $paused)) {
            return [
                'status' => 'needs_attention',
                'status_label' => __('Needs Attention'),
                'last_sync_label' => $latestSync instanceof Carbon ? $latestSync->diffForHumans() : null,
                'attention_message' => __('Sync needs attention'),
            ];
        }

        if ($connected instanceof GoogleSheetConnection) {
            return [
                'status' => 'connected',
                'status_label' => __('Connected'),
                'last_sync_label' => $latestSync instanceof Carbon ? $latestSync->diffForHumans() : null,
                'attention_message' => null,
            ];
        }

        if ($paused instanceof GoogleSheetConnection) {
            return [
                'status' => 'needs_attention',
                'status_label' => __('Paused'),
                'last_sync_label' => $latestSync instanceof Carbon ? $latestSync->diffForHumans() : null,
                'attention_message' => __('Connection paused'),
            ];
        }

        return [
            'status' => 'not_connected',
            'status_label' => __('Setup incomplete'),
            'last_sync_label' => null,
            'attention_message' => null,
        ];
    }

    /**
     * @return array{status: string, status_label: string, last_sync_label: string|null, attention_message: string|null}
     */
    private function facebookState(): array
    {
        /** @var FacebookPageConnection|null $connection */
        $connection = FacebookPageConnection::query()->latest('id')->first();

        if ($connection === null) {
            return $this->notConnectedState();
        }

        if (filled($connection->last_error) && in_array($connection->status, [
            FacebookPageConnectionStatus::Connected,
            FacebookPageConnectionStatus::Paused,
            FacebookPageConnectionStatus::Verified,
        ], true)) {
            return [
                'status' => 'needs_attention',
                'status_label' => __('Needs Attention'),
                'last_sync_label' => $connection->last_lead_at?->diffForHumans(),
                'attention_message' => __('Reconnect required'),
            ];
        }

        return match ($connection->status) {
            FacebookPageConnectionStatus::Connected => [
                'status' => 'connected',
                'status_label' => __('Connected'),
                'last_sync_label' => $connection->last_lead_at?->diffForHumans(),
                'attention_message' => null,
            ],
            FacebookPageConnectionStatus::Paused => [
                'status' => 'needs_attention',
                'status_label' => __('Paused'),
                'last_sync_label' => $connection->last_lead_at?->diffForHumans(),
                'attention_message' => __('Connection paused'),
            ],
            default => [
                'status' => 'not_connected',
                'status_label' => __('Setup incomplete'),
                'last_sync_label' => null,
                'attention_message' => null,
            ],
        };
    }

    /**
     * @return array{status: string, status_label: string, last_sync_label: string|null, attention_message: string|null}
     */
    private function notConnectedState(): array
    {
        return [
            'status' => 'not_connected',
            'status_label' => __('Not Connected'),
            'last_sync_label' => null,
            'attention_message' => null,
        ];
    }

    /**
     * @return list<array{group_label: string, time: string, description: string, category: string, sort: int}>
     */
    private function tenantActivityEvents(): array
    {
        $events = [];

        User::query()
            ->latest()
            ->limit(8)
            ->get(['name', 'created_at'])
            ->each(function (User $user) use (&$events): void {
                if (! $user->created_at instanceof Carbon) {
                    return;
                }

                $at = $user->created_at->timezone(config('app.timezone'));
                $events[] = [
                    'group_label' => $this->activityGroupLabel($at),
                    'time' => $at->format('g:i A'),
                    'description' => __('New user added: :name', ['name' => $user->name]),
                    'category' => 'users',
                    'sort' => $at->timestamp,
                ];
            });

        GoogleSheetConnection::query()
            ->whereNotNull('last_synced_at')
            ->latest('last_synced_at')
            ->limit(5)
            ->get(['name', 'last_synced_at'])
            ->each(function (GoogleSheetConnection $connection) use (&$events): void {
                if (! $connection->last_synced_at instanceof Carbon) {
                    return;
                }

                $at = $connection->last_synced_at->timezone(config('app.timezone'));
                $events[] = [
                    'group_label' => $this->activityGroupLabel($at),
                    'time' => $at->format('g:i A'),
                    'description' => __('Google Sheets synced: :name', ['name' => $connection->name]),
                    'category' => 'integrations',
                    'sort' => $at->timestamp,
                ];
            });

        Automation::query()
            ->whereNotNull('last_run_at')
            ->latest('last_run_at')
            ->limit(5)
            ->get(['name', 'last_run_at'])
            ->each(function (Automation $automation) use (&$events): void {
                if (! $automation->last_run_at instanceof Carbon) {
                    return;
                }

                $at = $automation->last_run_at->timezone(config('app.timezone'));
                $events[] = [
                    'group_label' => $this->activityGroupLabel($at),
                    'time' => $at->format('g:i A'),
                    'description' => __('Automation ran: :name', ['name' => $automation->name]),
                    'category' => 'system',
                    'sort' => $at->timestamp,
                ];
            });

        return $events;
    }

    private function activityGroupLabel(Carbon $at): string
    {
        if ($at->isToday()) {
            return __('Today');
        }

        if ($at->isYesterday()) {
            return __('Yesterday');
        }

        return $at->format('d M Y');
    }
}
