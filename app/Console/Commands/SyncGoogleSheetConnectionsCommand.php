<?php

namespace App\Console\Commands;

use App\Actions\SyncAllTenantGoogleSheets;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('google-sheets:sync')]
#[Description('Import new rows from connected Google Sheets into tenant CRM leads')]
class SyncGoogleSheetConnectionsCommand extends Command
{
    public function handle(SyncAllTenantGoogleSheets $syncAllTenantGoogleSheets): int
    {
        $result = $syncAllTenantGoogleSheets->handle();

        $this->components->info(__('Synced :tenants tenant(s): :created lead(s) created, :failed failure(s).', [
            'tenants' => $result['tenants'],
            'created' => $result['created'],
            'failed' => $result['failed'],
        ]));

        return self::SUCCESS;
    }
}
