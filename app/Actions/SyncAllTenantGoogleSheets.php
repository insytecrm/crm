<?php

namespace App\Actions;

use App\Enums\TenantStatus;
use App\Models\GoogleSheetConnection;
use App\Models\Tenant;
use Throwable;

class SyncAllTenantGoogleSheets
{
    public function __construct(private SyncGoogleSheetLeads $syncGoogleSheetLeads) {}

    /**
     * @return array{tenants: int, created: int, failed: int}
     */
    public function handle(): array
    {
        $tenantsSynced = 0;
        $created = 0;
        $failed = 0;

        Tenant::query()
            ->where('status', TenantStatus::Active)
            ->orderBy('id')
            ->each(function (Tenant $tenant) use (&$tenantsSynced, &$created, &$failed): void {
                $tenant->run(function () use (&$tenantsSynced, &$created, &$failed): void {
                    $connections = GoogleSheetConnection::query()
                        ->connected()
                        ->orderBy('id')
                        ->get();

                    if ($connections->isEmpty()) {
                        return;
                    }

                    $tenantsSynced++;

                    foreach ($connections as $connection) {
                        try {
                            $result = $this->syncGoogleSheetLeads->handle($connection);
                            $created += $result['created'];
                        } catch (Throwable) {
                            $failed++;
                        }
                    }
                });
            });

        return [
            'tenants' => $tenantsSynced,
            'created' => $created,
            'failed' => $failed,
        ];
    }
}
