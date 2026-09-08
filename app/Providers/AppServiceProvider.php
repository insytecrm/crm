<?php

namespace App\Providers;

use App\Contracts\AiChatClient;
use App\Contracts\ChannelPartnerProfileData;
use App\Contracts\DnsRecordVerifier;
use App\Contracts\GoogleSheetsClient;
use App\Contracts\MetaGraphClient;
use App\Contracts\PlatformDashboardData;
use App\Contracts\PlatformPlanCatalog;
use App\Support\Ai\FakeAiChatClient;
use App\Support\Ai\OpenAiChatClient;
use App\Support\Dns\PhpDnsRecordVerifier;
use App\Support\GoogleSheets\FakeGoogleSheetsClient;
use App\Support\GoogleSheets\GoogleSheetsApiClient;
use App\Support\Meta\FakeMetaGraphClient;
use App\Support\Meta\MetaGraphApiClient;
use App\Support\Platform\EloquentChannelPartnerProfileData;
use App\Support\Platform\EloquentPlatformDashboardData;
use App\Support\Platform\EloquentPlatformPlanCatalog;
use App\Support\Platform\TenantPlanAccess;
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
        $this->app->singleton(DnsRecordVerifier::class, PhpDnsRecordVerifier::class);
        $this->app->singleton(
            AiChatClient::class,
            $this->app->environment('testing') ? FakeAiChatClient::class : OpenAiChatClient::class,
        );
        $this->app->singleton(
            GoogleSheetsClient::class,
            $this->app->environment('testing') ? FakeGoogleSheetsClient::class : GoogleSheetsApiClient::class,
        );
        $this->app->singleton(
            MetaGraphClient::class,
            $this->app->environment('testing') ? FakeMetaGraphClient::class : MetaGraphApiClient::class,
        );
        $this->app->singleton(PlatformDashboardData::class, EloquentPlatformDashboardData::class);
        $this->app->singleton(PlatformPlanCatalog::class, EloquentPlatformPlanCatalog::class);
        $this->app->singleton(TenantPlanAccess::class);
        $this->app->singleton(ChannelPartnerProfileData::class, EloquentChannelPartnerProfileData::class);
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
