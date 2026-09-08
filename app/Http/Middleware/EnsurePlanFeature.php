<?php

namespace App\Http\Middleware;

use App\Support\Platform\TenantPlanAccess;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePlanFeature
{
    public function __construct(private TenantPlanAccess $access) {}

    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string $feature): Response
    {
        $this->access->abortUnlessFeature($feature);

        return $next($request);
    }
}
