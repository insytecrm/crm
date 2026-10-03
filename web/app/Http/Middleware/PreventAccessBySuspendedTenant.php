<?php

namespace App\Http\Middleware;

use App\Enums\TenantStatus;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PreventAccessBySuspendedTenant
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (tenant()?->status === TenantStatus::Suspended) {
            abort(403, 'This company account is suspended.');
        }

        return $next($request);
    }
}
