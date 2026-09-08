<?php

namespace App\Http\Middleware;

use App\Enums\PlanCapability;
use App\Enums\PropertyPortal;
use App\Support\Platform\TenantPlanAccess;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnforceTenantPlanAccess
{
    /**
     * @var array<string, string>
     */
    private const Features = [
        'tenant.ai' => 'insyte_ai',
        'tenant.leads.whatsapp' => 'whatsapp',
        'tenant.leads' => 'crm',
        'tenant.activities' => 'crm',
        'tenant.follow-ups' => 'crm',
        'tenant.site-visits' => 'crm',
        'tenant.tasks' => 'crm',
        'tenant.scheduled-events' => 'crm',
        'tenant.properties.microsite' => 'microsites',
        'tenant.properties' => 'properties',
        'tenant.bookings' => 'bookings',
        'tenant.revenue' => 'revenue',
        'tenant.invoices' => 'revenue',
        'tenant.payouts' => 'revenue',
        'tenant.reports' => 'reports',
        'tenant.teams' => 'teams',
        'tenant.team-chat' => 'team_inbox',
        'tenant.automations' => 'automations',
        'tenant.integrations' => 'integrations',
        'tenant.settings.integrations' => 'integrations',
        'tenant.settings.domains' => 'custom_domains',
    ];

    /**
     * @var array<string, string>
     */
    private const Capabilities = [
        'tenant.leads.duplicates' => 'crm.duplicates',
        'tenant.leads.export' => 'crm.export',
        'tenant.leads.import' => 'crm.import',
        'tenant.leads.documents' => 'crm.documents',
        'tenant.reports.analytics' => 'reports.analytics',
        'tenant.reports.export' => 'reports.export',
        'tenant.reports.print' => 'reports.print',
        'tenant.automations.templates' => 'automations.templates',
        'tenant.settings.integrations.api' => 'integration.api',
        'tenant.settings.integrations.google-sheets' => 'integration.google_sheets',
        'tenant.settings.integrations.facebook' => 'integration.facebook',
    ];

    public function __construct(private TenantPlanAccess $access) {}

    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $routeName = $request->route()?->getName();

        if (! is_string($routeName) || $routeName === '') {
            return $next($request);
        }

        $capability = $this->match($routeName, self::Capabilities);

        if ($routeName === 'tenant.settings.integrations.portal') {
            $capability = $this->portalCapability($request->route('portal'));
        }

        if ($capability !== null) {
            $this->access->abortUnlessCapability($capability);
        }

        $feature = $this->match($routeName, self::Features);

        if ($feature !== null) {
            $this->access->abortUnlessFeature($feature);
        }

        return $next($request);
    }

    /**
     * @param  array<string, string>  $map
     */
    private function match(string $routeName, array $map): ?string
    {
        $matched = null;
        $length = -1;

        foreach ($map as $prefix => $value) {
            if (str_starts_with($routeName, $prefix) && strlen($prefix) > $length) {
                $matched = $value;
                $length = strlen($prefix);
            }
        }

        return $matched;
    }

    private function portalCapability(mixed $portal): ?string
    {
        $value = is_string($portal) ? PropertyPortal::tryFrom($portal) : null;

        return match ($value) {
            PropertyPortal::NinetyNineAcres => PlanCapability::IntegrationNinetyNineAcres->value,
            PropertyPortal::Housing => PlanCapability::IntegrationHousing->value,
            PropertyPortal::MagicBricks => PlanCapability::IntegrationMagicBricks->value,
            PropertyPortal::NoBroker => PlanCapability::IntegrationNoBroker->value,
            default => null,
        };
    }
}
