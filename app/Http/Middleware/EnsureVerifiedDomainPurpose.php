<?php

namespace App\Http\Middleware;

use App\Enums\DomainPurpose;
use App\Models\Domain;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureVerifiedDomainPurpose
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next, string $purpose): Response
    {
        $domainPurpose = DomainPurpose::tryFrom($purpose);

        abort_if($domainPurpose === null, 404);

        $host = strtolower($request->getHost());

        $domain = Domain::query()
            ->where('domain', $host)
            ->purpose($domainPurpose)
            ->verified()
            ->first();

        abort_if($domain === null, 404);

        return $next($request);
    }
}
