<?php

namespace App\Http\Middleware;

use App\Enums\SubscriptionStatus;
use App\Models\PartnerSubscription;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PreventAccessByPausedSubscription
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $tenant = tenant();

        if ($tenant === null) {
            return $next($request);
        }

        $subscription = PartnerSubscription::query()
            ->where('tenant_id', $tenant->getTenantKey())
            ->latest('id')
            ->first();

        if (in_array($subscription?->status, [
            SubscriptionStatus::Paused,
            SubscriptionStatus::TrialEnded,
            SubscriptionStatus::Cancelled,
        ], true)) {
            abort(403, 'This company subscription is not active.');
        }

        return $next($request);
    }
}
