<?php

namespace App\Http\Middleware;

use App\Support\Platform\TenantPlanAccess;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePlanCapability
{
    public function __construct(private TenantPlanAccess $access) {}

    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string $capability): Response
    {
        $this->access->abortUnlessCapability($capability);

        return $next($request);
    }
}
