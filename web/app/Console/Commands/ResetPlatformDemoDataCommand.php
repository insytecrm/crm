<?php

namespace App\Console\Commands;

use App\Actions\ResetPlatformDemoData;
use Illuminate\Console\Command;

class ResetPlatformDemoDataCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'platform:reset-demo-data {--force : Confirm destructive reset of platform demo data}';

    /**
     * @var string
     */
    protected $description = 'Remove test channel partners and platform pipeline data while keeping super admin accounts';

    public function handle(ResetPlatformDemoData $resetPlatformDemoData): int
    {
        if (! $this->option('force')) {
            $this->components->warn('This removes all channel partners, leads, quotations, billing records, and subscriptions.');
            $this->components->info('Super admin accounts, plans, and platform utility settings are kept.');
            $this->components->error('Re-run with --force to continue.');

            return self::FAILURE;
        }

        $summary = $resetPlatformDemoData->handle();

        $this->components->info('Platform demo data reset complete.');
        $this->line("  Channel partners removed: {$summary['tenants_deleted']}");
        $this->line("  Super admins kept: {$summary['super_admins_kept']}");
        $this->line("  Other central users removed: {$summary['users_removed']}");

        return self::SUCCESS;
    }
}
