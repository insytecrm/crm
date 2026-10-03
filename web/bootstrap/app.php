<?php

use App\Http\Middleware\AuthenticateLeadApiToken;
use App\Http\Middleware\ConfigureSessionForContext;
use App\Http\Middleware\EnsurePlanCapability;
use App\Http\Middleware\EnsurePlanFeature;
use App\Http\Middleware\EnsureUserHasPermission;
use App\Http\Middleware\EnsureUserIsSuperAdmin;
use App\Http\Middleware\EnsureVerifiedDomainPurpose;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(prepend: [
            ConfigureSessionForContext::class,
        ]);

        $middleware->alias([
            'superadmin' => EnsureUserIsSuperAdmin::class,
            'permission' => EnsureUserHasPermission::class,
            'plan.feature' => EnsurePlanFeature::class,
            'plan.capability' => EnsurePlanCapability::class,
            'domain.purpose' => EnsureVerifiedDomainPurpose::class,
            'lead.api' => AuthenticateLeadApiToken::class,
        ]);

        $middleware->redirectGuestsTo(function (Request $request): string {
            if ($request->is('platform') || $request->is('platform/*')) {
                return route('login');
            }

            $tenant = $request->route('tenant') ?? $request->segment(1);

            if (is_string($tenant) && $tenant !== '' && ! str_contains($tenant, '.')) {
                return route('tenant.login', ['tenant' => $tenant]);
            }

            if (tenancy()->initialized) {
                return route('tenant.login');
            }

            return route('login');
        });

        $middleware->redirectUsersTo(function (Request $request): string {
            if ($request->is('platform') || $request->is('platform/*')) {
                return route('tenants.index');
            }

            $tenant = $request->route('tenant') ?? $request->segment(1);

            if (is_string($tenant) && $tenant !== '' && ! str_contains($tenant, '.')) {
                return route('tenant.dashboard', ['tenant' => $tenant]);
            }

            if (tenancy()->initialized) {
                return route('tenant.dashboard');
            }

            return route('tenants.index');
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
