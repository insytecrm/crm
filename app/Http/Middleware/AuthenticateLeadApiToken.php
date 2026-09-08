<?php

namespace App\Http\Middleware;

use App\Enums\TenantStatus;
use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateLeadApiToken
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if (! is_string($token) || $token === '') {
            abort(401, __('API key is required.'));
        }

        $tenant = Tenant::query()
            ->where('lead_api_token_hash', hash('sha256', $token))
            ->first();

        if ($tenant === null) {
            abort(401, __('Invalid API key.'));
        }

        if ($tenant->status === TenantStatus::Suspended) {
            abort(403, __('This company account is suspended.'));
        }

        tenancy()->initialize($tenant);

        $tenant->forceFill([
            'lead_api_token_last_used_at' => now(),
        ])->save();

        return $next($request);
    }
}
