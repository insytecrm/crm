<?php

namespace App\Actions;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ResetPlatformDemoData
{
    /**
     * @return array{
     *     tenants_deleted: int,
     *     super_admins_kept: int,
     *     users_removed: int,
     * }
     */
    public function handle(): array
    {
        $this->clearPlatformOperationalData();

        $tenantsDeleted = 0;

        Tenant::query()
            ->orderBy('id')
            ->get()
            ->each(function (Tenant $tenant) use (&$tenantsDeleted): void {
                $tenant->delete();
                $tenantsDeleted++;
            });

        $superAdminIds = User::query()
            ->where('is_super_admin', true)
            ->pluck('id');

        $usersRemoved = User::query()
            ->where('is_super_admin', false)
            ->delete();

        app(SeedDefaultPlans::class)->handle();

        return [
            'tenants_deleted' => $tenantsDeleted,
            'super_admins_kept' => $superAdminIds->count(),
            'users_removed' => $usersRemoved,
        ];
    }

    private function clearPlatformOperationalData(): void
    {
        Schema::disableForeignKeyConstraints();

        foreach ([
            'billing_refunds',
            'billing_discounts',
            'billing_payments',
            'billing_invoices',
            'quotations',
            'partner_subscriptions',
            'platform_lead_activities',
            'platform_lead_notes',
            'platform_leads',
            'landing_submissions',
            'facebook_page_registrations',
            'portal_webhook_endpoints',
        ] as $table) {
            if (Schema::hasTable($table)) {
                DB::table($table)->delete();
            }
        }

        Schema::enableForeignKeyConstraints();
    }
}
