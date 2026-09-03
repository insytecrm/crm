<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Stancl\Tenancy\DatabaseConfig;
use Stancl\Tenancy\Middleware\InitializeTenancyByPath;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        InitializeTenancyByPath::$onFail = fn () => abort(404);

        DatabaseConfig::generateDatabaseNamesUsing(function ($tenant): string {
            $name = config('tenancy.database.prefix').$tenant->getTenantKey().config('tenancy.database.suffix');
            $connection = config('tenancy.database.template_tenant_connection') ?: config('database.default');
            $driver = config("database.connections.{$connection}.driver");

            if ($driver === 'sqlite') {
                return $name.'.sqlite';
            }

            return $name;
        });
    }
}
