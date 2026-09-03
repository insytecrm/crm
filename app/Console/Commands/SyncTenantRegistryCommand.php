<?php

namespace App\Console\Commands;

use App\Enums\TenantStatus;
use App\Models\Tenant;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

#[Signature('tenants:sync-registry')]
#[Description('Register tenant records in the central database for existing tenant databases')]
class SyncTenantRegistryCommand extends Command
{
    public function handle(): int
    {
        $connection = config('tenancy.database.central_connection') ?: config('database.default');
        $prefix = (string) config('tenancy.database.prefix', 'tenant');
        $suffix = (string) config('tenancy.database.suffix', '');
        $driver = config("database.connections.{$connection}.driver");

        if ($driver === 'sqlite') {
            $this->components->warn('Tenant registry sync is only supported for MySQL/MariaDB/PostgreSQL.');

            return self::FAILURE;
        }

        $registered = 0;

        foreach ($this->tenantDatabaseNames($connection, $prefix) as $databaseName) {
            if (! str_starts_with($databaseName, $prefix)) {
                continue;
            }

            $tenantId = substr($databaseName, strlen($prefix));

            if ($suffix !== '' && str_ends_with($tenantId, $suffix)) {
                $tenantId = substr($tenantId, 0, -strlen($suffix));
            }

            if ($tenantId === '' || in_array($tenantId, Tenant::ReservedIds, true)) {
                continue;
            }

            if (! preg_match('/^[a-z0-9](?:[a-z0-9-]*[a-z0-9])?$/', $tenantId)) {
                $this->components->warn("Skipped database [{$databaseName}] because [{$tenantId}] is not a valid tenant slug.");

                continue;
            }

            if (Tenant::query()->whereKey($tenantId)->exists()) {
                continue;
            }

            Tenant::withoutEvents(function () use ($tenantId): void {
                Tenant::query()->create([
                    'id' => $tenantId,
                    'name' => Str::headline(str_replace('-', ' ', $tenantId)),
                    'email' => null,
                    'status' => TenantStatus::Active->value,
                ]);
            });

            $this->components->info("Registered tenant [{$tenantId}] from database [{$databaseName}].");
            $registered++;
        }

        if ($registered === 0) {
            $this->components->info('No missing tenant records were found.');

            return self::SUCCESS;
        }

        $this->components->info("Registered {$registered} tenant(s).");

        return self::SUCCESS;
    }

    /**
     * @return list<string>
     */
    private function tenantDatabaseNames(string $connection, string $prefix): array
    {
        $driver = config("database.connections.{$connection}.driver");

        if ($driver === 'mysql' || $driver === 'mariadb') {
            $like = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $prefix).'%';
            $databases = DB::connection($connection)->select("SHOW DATABASES LIKE '{$like}'");

            return array_values(array_map(
                fn (object $row): string => (string) ($row->Database ?? array_values((array) $row)[0]),
                $databases,
            ));
        }

        if ($driver === 'pgsql') {
            $databases = DB::connection($connection)->select(
                'SELECT datname FROM pg_database WHERE datname LIKE ?',
                [$prefix.'%'],
            );

            return array_values(array_map(
                fn (object $row): string => (string) $row->datname,
                $databases,
            ));
        }

        return [];
    }
}
