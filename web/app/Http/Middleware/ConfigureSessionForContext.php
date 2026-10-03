<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ConfigureSessionForContext
{
    /**
     * Isolate platform and tenant sessions on the same host.
     *
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $segment = $request->segment(1);

        if ($segment === 'platform') {
            config([
                'session.path' => '/platform',
                'session.cookie' => 'platform_session',
            ]);

            return $next($request);
        }

        if (is_string($segment) && $segment !== '' && ! in_array($segment, ['up', 'livewire', 'storage', 'build'], true)) {
            config([
                'session.path' => '/'.$segment,
                'session.cookie' => 'tenant_'.$segment.'_session',
            ]);
        }

        return $next($request);
    }
}
